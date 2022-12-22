<?php

namespace App\Test\Integration\Authentication;

use App\Domain\Security\Enum\SecurityType;
use App\Domain\Security\Exception\SecurityException;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * In this class actions that are processed by the `SecurityService` are tested.
 */
class LoginSecurityTest extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use RouteTestTrait;

    /**
     * Test thresholds and according delays of login failures
     * If login request amount exceeds threshold, the user has to wait a certain delay.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return void
     */
    public function testLoginThrottlingWrongCredentials(): void
    {
        // If more than x percentage of global login requests are wrong, there is an exception but that won't happen
        // while testing as there is a minimal hard limit on allowed failed login requests

        $password = '12345678';
        $email = 'user@exmple.com';
        $correctLoginRequestBody = ['email' => $email, 'password' => $password];

        // Insert user fixture
        $user = $this->insertFixturesWithAttributes([
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ], UserFixture::class);

        // Login request body with invalid credentials
        $loginRequestBody = ['email' => 'wrong@email.com', 'password' => 'wrong_password'];

        // Login request with incorrect credentials
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $loginRequestBody);

        $throttleRules = $this->container->get('settings')['security']['login_throttle_rule'];

        $lowestThreshold = array_key_first($throttleRules);

        // It should be tested with the most strict throttle as well. This means that last run should match last threshold
        $thresholdForStrictestThrottle = array_key_last($throttleRules);

        // Reverse throttleRules for the loop iterations so that it will check for the big (stricter) delays first
        krsort($throttleRules);

        // Simulate amount of login requests to go through every throttling level
        // $nthLoginRequest is the current nth amount of login request that is made (4th, 5th, 6th etc.)
        for ($nthLoginRequest = 1; $nthLoginRequest <= $thresholdForStrictestThrottle; $nthLoginRequest++) {
            // * Until the lowest threshold is reached, the login requests are normal and should not be throttled
            if ($nthLoginRequest < $lowestThreshold) {
                // As long as the nth request is below first threshold no exception is thrown but user_request entry is
                // added which is needed to test throttling in the next iterations
                $response = $this->app->handle($request);
                self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
                continue; // Skip to next loop iteration
            }

            // * Lowest threshold reached, assert that correct throttle is applied
            foreach ($throttleRules as $threshold => $delay) {
                // Apply correct throttling rule in relation to nth login request by checking which threshold is reached
                if ($nthLoginRequest >= $threshold) {
                    // If the amount of made login requests reaches the first threshold -> throttle
                    try {
                        // Creates an additional user_request entry that is needed to assert for next iterations with
                        // higher throttling
                        $this->app->handle($request);
                        self::fail(
                            'SecurityException should be thrown' .
                            "\nnthLoginrequest: $nthLoginRequest, threshold: $threshold"
                        );
                    } catch (SecurityException $se) {
                        self::assertEqualsWithDelta(
                            $delay,
                            $se->getRemainingDelay(),
                            1,
                            'nth login: ' . $nthLoginRequest . ' threshold: ' . $threshold
                        );
                        self::assertSame(SecurityType::USER_LOGIN, $se->getSecurityType());

                        // ?Test that user can make a new request that is processed after having waited the delay
                        // Below this request after waiting the delay is with WRONG credentials
                        if (is_numeric($delay)) {
                            // After waiting the delay, user is allowed to make new login request but if the credentials
                            // are wrong again (using same $request), an exception is expected from the **second
                            // security check** after the failed request in LoginVerifier.
                            // (This second security check is not done if request has correct credentials)
                            // Prepone last user request to simulate waiting
                            $this->preponeLastUserRequestEntry($delay);
                            try {
                                // Request again with wrong credentials
                                $this->app->handle($request);
                                self::fail(
                                    'SecurityException should be thrown after trying to login again with wrong creds even after waiting for the delay' .
                                    "\nnthLoginrequest: $nthLoginRequest, threshold: $threshold"
                                );
                            } catch (SecurityException $se) {
                                // Expect exception, and we cant use $this->expectException(); as it finishes the test
                                // Delete newly created login request as the request above created a new entry in
                                // user_request, and the login stats would be falsified in the next iterations/tests
                                $this->deleteLastUserRequestEntry();
                            }

                            // * Assert that after waiting the delay, a successful request can be made with correct credentials
                            $requestAfterWaitingDelay = $this->createFormRequest(
                                'POST',
                                $this->urlFor('login-submit'),
                                $correctLoginRequestBody
                            );
                            $responseAfterWaiting = $this->app->handle($requestAfterWaitingDelay);
                            // Again delete the most recent user_request entry as the request above is also an "aside"
                            // test that should not influence login stats of the next iterations.
                            $this->deleteLastUserRequestEntry();

                            // Assert that user is logged in and redirected
                            self::assertSame(
                                $user['id'],
                                $this->container->get(SessionInterface::class)->get('user_id')
                            );
                            self::assertSame(
                                StatusCodeInterface::STATUS_FOUND,
                                $responseAfterWaiting->getStatusCode()
                            );

                            // Reset last login request so that it's correct for the next request as security check is
                            // done before the new `user_request` entry is made.
                            $this->postponeLastUserRequestEntry($delay);
                        }
                    }
                    // If right threshold is reached and asserted, go out of nthLogin loop to test the next iteration
                    // Otherwise the throttling foreach continues and the delay assertion fails as it would be too small
                    // once nthLoginRequest reaches the second-strictest throttling.
                    continue 2;
                }
            }
        }
        ksort($throttleRules);
    }

    /**
     * Prepone last request time to simulate waiting delay.
     *
     * @param int $seconds
     *
     * @return void
     */
    private function preponeLastUserRequestEntry(int $seconds): void
    {
        // Change row with the highest id
        $query = "UPDATE user_request SET created_at = DATE_SUB(created_at, INTERVAL $seconds SECOND) ORDER BY id DESC LIMIT 1";
        $this->createQueryStatement($query);
    }

    /**
     * Delete most recent request.
     *
     * @return void
     */
    private function deleteLastUserRequestEntry(): void
    {
        // Change row with the highest id
        $query = 'DELETE FROM user_request ORDER BY id DESC LIMIT 1';
        $this->createQueryStatement($query);
    }

    /**
     * Postpone last request (the last request has to be reset to its initial value as security check is done before
     * the `user_request` of the next login request is inserted.
     *
     * @param int $seconds
     *
     * @return void
     */
    private function postponeLastUserRequestEntry(int $seconds): void
    {
        // Change row with the highest id
        $query = "UPDATE user_request SET created_at = DATE_SUB(created_at, INTERVAL $seconds SECOND) ORDER BY id DESC LIMIT 1";
        $this->createQueryStatement($query);

        // $sql = "SELECT * FROM user_request ORDER BY id DESC LIMIT 1";
        // $statement = $this->createPreparedStatement($sql);
        // $statement->execute();
        // $r = $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Test thresholds and according delays with login successes
     * If login request amount exceeds threshold, the user has to wait a certain delay.
     * For login requests with correct credentials, the only security check is done before processing the request
     * For invalid login requests an additional security check is done after the request. This means that if the
     * first threshold is 4, the 4th valid login request does not throw exception but the 4th invalid login request
     * This changes the test behaviour too much to test both in the same function hence separated in 2.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLoginThrottlingCorrectCredentials(): void
    {
        // If more than x percentage of global login requests are wrong, there is an exception but that won't happen
        // while testing as there is a minimal hard limit on allowed failed login requests

        $password = '12345678';
        $email = 'user@exmple.com';
        // Insert user fixture
        $user = $this->insertFixturesWithAttributes([
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ], UserFixture::class);
        // Login request body
        $correctLoginRequestBody = ['email' => $email, 'password' => $password];

        // Prepare login request with valid credentials
        // First difference (#diff1) to the rest with incorrect credentials
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $correctLoginRequestBody);

        $throttleRules = $this->container->get('settings')['security']['login_throttle_rule'];

        $lowestThreshold = array_key_first($throttleRules);

        // It should be tested with the most strict throttle as well. This means that last run should match last threshold
        // For this +1 is added as exception is only thrown at beginning of next request for login successes #diff2
        $thresholdForStrictestThrottle = array_key_last($throttleRules) + 1;

        // Reverse throttleRules for the loop iterations so that it will check for the big (stricter) delays first
        krsort($throttleRules);

        // Simulate amount of login requests to go through every throttling level
        // $nthLoginRequest is the current nth amount of login request that is made (4th, 5th, 6th etc.)
        for ($nthLoginRequest = 1; $nthLoginRequest <= $thresholdForStrictestThrottle; $nthLoginRequest++) {
            // * Until the lowest threshold is reached, the login requests are normal and should not be throttled
            // #diff3 to test with wrong credentials is the <= operator here instead of <
            if ($nthLoginRequest <= $lowestThreshold) {
                // As long as the nth request is below first threshold no exception is thrown but user_request entry is
                // added which is needed later for the throttling
                $response = $this->app->handle($request);
                self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
                continue; // leave foreach loop
            }

            // * Lowest threshold reached, assert that correct throttle is applied
            foreach ($throttleRules as $threshold => $delay) {
                // Apply correct throttling rule in relation to nth login request by checking which threshold is reached
                // #diff4 to test with wrong credentials is the > operator here instead of >=
                if ($nthLoginRequest > $threshold) {
                    // If the amount of made login requests reaches the first threshold -> throttle
                    try {
                        // echo "\n threshold: $threshold, delay: $delay";
                        $this->app->handle($request);
                        // If exception is not thrown, get user request stats
                        $sql = 'SELECT * FROM user_request';
                        $statement = $this->createPreparedStatement($sql);
                        $statement->execute();
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                        self::fail(
                            'SecurityException should be thrown' .
                            "\nnthLoginrequest: $nthLoginRequest, threshold: $threshold\n" . json_encode($result)
                        );
                    } catch (SecurityException $se) {
                        self::assertEqualsWithDelta(
                            $delay,
                            $se->getRemainingDelay(),
                            1,
                            'nth login: ' . $nthLoginRequest . ' threshold: ' . $threshold
                        );
                        self::assertSame(SecurityType::USER_LOGIN, $se->getSecurityType());

                        // * Assert that after waiting the delay, a successful request can be made with correct credentials
                        // * Also adds new success request which is needed as when security exception is thrown (like
                        // * in this catch block) a new `user_request` entry is NOT made #diff5
                        // If new delay after one more trial
                        if (is_numeric($delay)) {
                            // Prepone last request to simulate the new waiting delay caused from
                            $this->preponeLastUserRequestEntry($delay);

                            $requestAfterWaitingDelay = $this->createFormRequest(
                                'POST',
                                $this->urlFor('login-submit'),
                                $correctLoginRequestBody
                            );
                            $responseAfterWaiting = $this->app->handle($requestAfterWaitingDelay);

                            // Assert that user is logged in and redirected
                            self::assertSame(
                                $user['id'],
                                $this->container->get(SessionInterface::class)->get('user_id')
                            );
                            self::assertSame(StatusCodeInterface::STATUS_FOUND, $responseAfterWaiting->getStatusCode());

                            // This additional successful request from the request above should NOT be deleted nor
                            // postponed as it's needed to increase user_request entries for the next iterations #diff6
                        }
                    }
                    // If right threshold is reached and asserted, go out of nthLogin loop to test the next iteration
                    // Otherwise the throttling foreach continues and the delay assertion fails as it would be too small
                    // once nthLoginRequest reaches the second-strictest throttling.
                    continue 2;
                }
            }
        }
        ksort($throttleRules);
    }

    // /**
    //  * Test thresholds and according delays once with failed logins and once with successes
    //  * If login request amount exceeds threshold, the user has to wait a certain delay.
    //  *
    //  * Provide status and partial email content for login test user that is not active
    //  * In provider mainly to reset database between correct and incorrect requests.
    //  * @dataProvider \App\Test\Provider\Authentication\AuthenticationProvider::authenticationSecurityCases()
    //  *
    //  * @param bool $credentialsAreCorrect bool if cred are correct
    //  * @param int $statusCode unauthorized or found
    //  *
    //  * @throws ContainerExceptionInterface
    //  * @throws NotFoundExceptionInterface
    //  */
    // public function testLoginThrottling_correctAndWrongCreds(bool $credentialsAreCorrect, int $statusCode): void
    // {
    //     $loggerFactory = $this->container->get(LoggerFactory::class);
    //     $logger = $loggerFactory->addFileHandler('debug.log')->createInstance('auth-test');
    //
    //     // If more than x percentage of global login requests are wrong, there is an exception but that won't happen
    //     // while testing as there is a minimal hard limit on allowed failed login requests
    //
    //     $password = '12345678';
    //     $email = 'user@exmple.com';
    //     // Insert user fixture
    //     $user = $this->insertFixturesWithAttributes([
    //         'email' => $email,
    //         'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    //     ], UserFixture::class);
    //     // Login request body
    //     $correctLoginRequestBody = ['email' => $email, 'password' => $password];
    //     $loginRequestBody = $correctLoginRequestBody;
    //     if ($credentialsAreCorrect === false) {
    //         $loginRequestBody = ['email' => 'wrong@email.com', 'password' => 'wrong_password'];
    //     }
    //     // Login request once with correct credentials, once with incorrect
    //     // Prepare login request with either valid or invalid credentials
    //     $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $loginRequestBody);
    //
    //     $throttleRules = $this->container->get('settings')['security']['login_throttle_rule'];
    //
    //     $lowestThreshold = array_key_first($throttleRules);
    //
    //     // It should be tested with the most strict throttle as well. This means that last run should match last threshold
    //     // Previously +1 had to be added as exception was only thrown on beginning next request but now the login
    //     // security check is also done after an unsuccessful request
    //     $thresholdForStrictestThrottle = array_key_last($throttleRules);
    //     // Increment $thresholdForStrictestThrottle by 1 if $credentialsAreCorrect is true to test captcha as well
    //     $thresholdForStrictestThrottle += $credentialsAreCorrect ? 1 : 0;
    //
    //     // Reverse throttleRules for the loop iterations so that it will check for the big (stricter) delays first
    //     krsort($throttleRules);
    //
    //     // Simulate amount of login requests to go through every throttling level
    //     // $nthLoginRequest is the current nth amount of login request that is made (4th, 5th, 6th etc.)
    //     for ($nthLoginRequest = 1; $nthLoginRequest <= $thresholdForStrictestThrottle; $nthLoginRequest++) {
    //         // * If credentials are correct, remove 1 from $nthLoginRequest. The reason is the following:
    //         // For login requests with correct credentials, the only security check is done before processing the request
    //         // For invalid login requests an additional security check is done after the request. This means that if the
    //         // first threshold is 4, the 4th valid login request does not throw exception but the 4th invalid login request
    //         // will throw an exception. By decreasing the $nthLoginRequest, the same assertion logic can be used later
    //         echo "\nnthLoginRequest: $nthLoginRequest";
    //         if ($credentialsAreCorrect === true) {
    //             $nthLoginRequest--;
    //         }
    //
    //         // * Until the lowest threshold is reached, the login requests are normal and should not be throttled
    //         if ($nthLoginRequest < $lowestThreshold) {
    //             // As long as the nth request is below first threshold no exception is thrown but user_request entry is
    //             // added which is needed later for the throttling
    //             $response = $this->app->handle($request);
    //             self::assertSame($statusCode, $response->getStatusCode());
    //             if ($credentialsAreCorrect === true) {
    //                 // Add 1 to nthLoginRequest to have the correct number in the next for loop iteration
    //                 $nthLoginRequest++;
    //             }
    //             continue; // leave foreach loop
    //         }
    //
    //         // * Lowest threshold reached, assert that correct throttle is applied
    //         foreach ($throttleRules as $threshold => $delay) {
    //             // Apply correct throttling rule in relation to nth login request by checking which threshold is reached
    //             if ($nthLoginRequest >= $threshold) {
    //                 // If the amount of made login requests reaches the first threshold -> throttle
    //                 try {
    //                     echo "\nthreshold: $threshold, delay: $delay";
    //                     if ($nthLoginRequest === 9 && $threshold === 9) {
    //                         $a = 1;
    //                     }
    //                     $this->app->handle($request);
    //                     self::fail(
    //                         'SecurityException should be thrown' .
    //                         "\nnthLoginrequest: $nthLoginRequest, threshold: $threshold"
    //                     );
    //                 } catch (SecurityException $se) {
    //                     self::assertEqualsWithDelta(
    //                         $delay,
    //                         $se->getRemainingDelay(),
    //                         1,
    //                         'nth login: ' . $nthLoginRequest . ' threshold: ' . $threshold
    //                     );
    //                     self::assertSame(SecurityType::USER_LOGIN, $se->getSecurityType());
    //
    //                     // ?Test that user can make a new request after having waited the delay
    //                     $delayForRequestWithCorrectCredentials = $delay;
    //                     // Below this request after waiting the delay is with WRONG credentials
    //                     if (is_numeric($delay) && $credentialsAreCorrect === false) {
    //                         // After waiting the delay, user is allowed to make new login request but if the credentials
    //                         // are wrong again (using same $request), an exception is expected from the second security check
    //                         // after the failed request in LoginVerifier (which is not done if request has correct credentials)
    //                         // Prepone last user request to simulate waiting
    //                         $this->preponeLastUserRequestEntry($delay);
    //                         try {
    //                             // Request with wrong credentials
    //                             $this->app->handle($request);
    //                             self::fail(
    //                                 'SecurityException should be thrown after trying to login again with wrong creds even after waiting for the delay' .
    //                                 "\nnthLoginrequest: $nthLoginRequest, threshold: $threshold"
    //                             );
    //                         } catch (SecurityException $se) {
    //                             // Expect exception, and we cant use $this->expectException(); as it finishes the test
    //                         }
    //                         // Reset last login request so that it's correct for the next request as security check is
    //                         // done before the new `user_request` entry is made.
    //                         // $postponeLastUserRequestEntry($delay);
    //
    //                         // Above a new unsuccessful request is made. As it's an additional request, the delay has
    //                         // to be increased to the next threshold when it's at the tipping point which is the case
    //                         // when the nth login request + 1 exists as key (threshold) in $throttleRules
    //                         $delayForRequestWithCorrectCredentials = $throttleRules[$nthLoginRequest + 1] ?? $delay;
    //                     }
    //
    //                     //? Test user making a successful request after having waited the delay
    //                     // If new delay after one more trial
    //                     if (is_numeric($delayForRequestWithCorrectCredentials) && false) {
    //                         // Prepone last request to simulate the new waiting delay caused from
    //                         $this->preponeLastUserRequestEntry($delayForRequestWithCorrectCredentials);
    //
    //                         // * Assert that after waiting the delay, a successful request can be made with correct credentials
    //                         $requestAfterWaitingDelay = $this->createFormRequest(
    //                             'POST',
    //                             $this->urlFor('login-submit'),
    //                             $correctLoginRequestBody
    //                         );
    //                         $responseAfterWaiting = $this->app->handle($requestAfterWaitingDelay);
    //                         // Assert that user is logged in and redirected
    //                         self::assertSame(
    //                             $user['id'],
    //                             $this->container->get(SessionInterface::class)->get('user_id')
    //                         );
    //                         self::assertSame(StatusCodeInterface::STATUS_FOUND, $responseAfterWaiting->getStatusCode());
    //                         $this->postponeLastUserRequestEntry($delayForRequestWithCorrectCredentials);
    //                     }
    //                 }
    //                 if ($credentialsAreCorrect === true) {
    //                     // Add 1 to nthLoginRequest to have the correct number in the next for loop iteration
    //                     $nthLoginRequest++;
    //                 }
    //                 // If right threshold is reached and asserted, go out of nthLogin loop to test the next iteration
    //                 // Otherwise the throttling foreach continues and the delay assertion fails as it would be too small
    //                 // once nthLoginRequest reaches the second-strictest throttling.
    //                 continue 2;
    //             }
    //         }
    //     }
    //     ksort($throttleRules);

    // The above loop roughly has the same checks as the follows (the test function was since refactored so a lot
    // will be different
    /*        for ($nthLoginRequest = 1; $nthLoginRequest <= 12; $nthLoginRequest++) {
                // SecurityCheck is done in beginning of next request so if threshold is 4, check that'll fail will be done
                // in the 5th request
                if ($nthLoginRequest <= 4) {
                    // no exception expected
                    $this->app->handle($request);
                } // First the highest threshold otherwise lowest would always match and it'd never go in the next ones
                elseif ($nthLoginRequest > 12) {// first throttle expected so 10s (after 4 failures)
                    try {
                        $this->app->handle($request);
                        self::fail('SecurityException should be thrown');
                    } catch (SecurityException $se) {
                        self::assertSame('captcha', $se->getRemainingDelay());
                        self::assertSame(SecurityType::USER_LOGIN, $se->getType());
                    }
                } // If threshold is 12, check that'll fail will be done in the 13th request
                elseif ($nthLoginRequest > 9) {// second throttle expected so 120s
                    try {
                        $this->app->handle($request);
                        self::fail('SecurityException should be thrown');
                    } catch (SecurityException $se) {
                        self::assertEqualsWithDelta(120, $se->getRemainingDelay(), 1);
                        self::assertSame(SecurityType::USER_LOGIN, $se->getType());
                        // Reset to time to simulate waiting
                        $requestPreponerRepository->preponeLastRequest(120);
                    }

                    // After waiting the delay, user is allowed to make new login request
                    $responseAfterWaiting = $this->app->handle($request);
                    // Assert that request was a login request with invalid credentials
                    self::assertSame(401, $responseAfterWaiting->getStatusCode());
                    // SecurityException will not be thrown after invalid login as check happens in beginning of next request
                } elseif ($nthLoginRequest > 4) {// captcha expected
                    try {
                        $response = $this->app->handle($request);
                        self::fail('SecurityException should be thrown');
                    } catch (SecurityException $se) {
                        self::assertEqualsWithDelta(10, $se->getRemainingDelay(), 1);
                        self::assertSame(SecurityType::USER_LOGIN, $se->getType());
                        // Reset to time to simulate waiting
                        $requestPreponerRepository->preponeLastRequest(10);
                    }

                    // After waiting the delay, user is allowed to make new login request
                    $responseAfterWaiting = $this->app->handle($request);
                    // Assert that request was a login request with invalid credentials
                    self::assertSame(401, $responseAfterWaiting->getStatusCode());
                    // SecurityException will not be thrown after invalid login as check happens in beginning of next request
                }
            }*/
    // }
}
