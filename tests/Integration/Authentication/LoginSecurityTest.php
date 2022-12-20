<?php

namespace App\Test\Integration\Authentication;

use App\Domain\Security\Enum\SecurityType;
use App\Domain\Security\Exception\SecurityException;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\FixtureTestTrait;
use App\Test\Traits\RouteTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Selective\TestTrait\Traits\DatabaseTestTrait;

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
     * Test thresholds and according delays once with failed logins and once with successes
     * If login request amount exceeds threshold, the user has to wait a certain delay.
     *
     * @dataProvider \App\Test\Provider\Authentication\AuthenticationProvider::authenticationSecurityCases()
     *
     * @param bool $credentialsAreCorrect
     * @param int $statusCode
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLoginThrottling(bool $credentialsAreCorrect, int $statusCode): void
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
        $loginRequestBody = $correctLoginRequestBody;
        if ($credentialsAreCorrect === false) {
            $loginRequestBody = ['email' => 'wrong@email.com', 'password' => 'wrong_password'];
        }
        // Login request once with correct credentials, once with incorrect
        // Prepare login request with either valid or invalid credentials
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $loginRequestBody);

        // Function to prepone last request time to simulate waiting delay
        $preponeLastUserRequestEntry = function ($seconds) {
            // Change row with the highest id
            $query = "UPDATE user_request SET created_at = DATE_SUB(NOW(), INTERVAL $seconds SECOND) ORDER BY id DESC LIMIT 1";
            $this->createQueryStatement($query);
        };

        $throttleRules = $this->container->get('settings')['security']['login_throttle_rule'];

        $lowestThreshold = array_key_first($throttleRules);

        // It should be tested with the most strict throttle as well. This means that last run should match last threshold
        // Previously +1 had to be added as exception was only thrown on beginning next request but now the login
        // security check is also done after an unsuccessful request
        $thresholdForStrictestThrottle = array_key_last($throttleRules);

        // Revers throttleRules for the loop iterations so that it will check for the big (stricter) delays first
        krsort($throttleRules);

        // Simulate amount of login requests to go through every throttling level
        // $nthLoginRequest is the current nth amount of login request that is made (4th, 5th, 6th etc.)
        for ($nthLoginRequest = 1; $nthLoginRequest <= $thresholdForStrictestThrottle; $nthLoginRequest++) {
            // * If credentials are correct, remove 1 from $nthLoginRequest. The reason is the following:
            // For login requests with correct credentials, the only security check is done before processing the request
            // For invalid login requests an additional security check is done after the request. This means that if the
            // first threshold is 4, the 4th valid login request does not throw exception but the 4th invalid login request
            // will throw an exception. By decreasing the $nthLoginRequest, the same assertion logic can be used later
            if ($credentialsAreCorrect === true) {
                $nthLoginRequest--;
            }

            // * Until the lowest threshold is reached, the login requests are normal and should not be throttled
            if ($nthLoginRequest < $lowestThreshold) {
                // As long as the nth request is below first threshold no exception is thrown but user_request entry is
                // added which is needed later for the throttling
                $response = $this->app->handle($request);
                self::assertSame($statusCode, $response->getStatusCode());
                if ($credentialsAreCorrect === true) {
                    // Add 1 to nthLoginRequest to have the correct number in the next for loop iteration
                    $nthLoginRequest++;
                }
                continue; // leave foreach loop
            }

            // * Lowest threshold reached, assert that correct throttle is applied
            foreach ($throttleRules as $threshold => $delay) {
                // Apply correct throttling rule in relation to nth login request by checking which threshold is reached
                if ($nthLoginRequest >= $threshold) {
                    // If the amount of made login requests reaches the first threshold -> throttle
                    try {
                        $this->app->handle($request);
                        self::fail(
                            'SecurityException should be thrown' . "\nnthLoginrequest: $nthLoginRequest, threshold: 
                            $threshold"
                        );
                    } catch (SecurityException $se) {
                        self::assertEqualsWithDelta(
                            $delay,
                            $se->getRemainingDelay(),
                            1,
                            'nth login: ' .
                            $nthLoginRequest . ' threshold: ' . $threshold
                        );
                        self::assertSame(SecurityType::USER_LOGIN, $se->getSecurityType());
                    }

                    // Prepone last user request to simulate waiting except if $delay is not numeric (captcha)
                    if (is_numeric($delay)) {
                        // Reset to time to simulate waiting
                        $preponeLastUserRequestEntry($delay);

                        // After waiting the delay, user is allowed to make new login request but if the credentials
                        // are wrong again (using same $request), an exception is expected from the second security check
                        // after the failed request in LoginVerifier (which is not done if request has correct credentials)
                        if ($credentialsAreCorrect === false) {
                            $this->expectException(SecurityException::class);
                            $this->app->handle($request);
                        }

                        // * Assert that after waiting the delay, a successful request can be made with correct credentials
                        $requestAfterWaitingDelay = $this->createFormRequest(
                            'POST',
                            $this->urlFor('login-submit'),
                            $correctLoginRequestBody
                        );
                        $responseAfterWaiting = $this->app->handle($requestAfterWaitingDelay);
                        // Assert that user is logged in and redirected
                        self::assertSame($user['id'], $this->container->get(SessionInterface::class)->get('user_id'));
                        self::assertSame(StatusCodeInterface::STATUS_FOUND, $responseAfterWaiting->getStatusCode());
                    }
                    if ($credentialsAreCorrect === true) {
                        // Add 1 to nthLoginRequest to have the correct number in the next for loop iteration
                        $nthLoginRequest++;
                    }
                    // If right threshold is reached and asserted, go out of nthLogin loop to test the next iteration
                    // Otherwise the throttling foreach continues and the delay assertion fails as it would be too small
                    // once nthLoginRequest reaches the second-strictest throttling.
                    continue 2;
                }
            }
        }
        ksort($throttleRules);

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
    }
}
