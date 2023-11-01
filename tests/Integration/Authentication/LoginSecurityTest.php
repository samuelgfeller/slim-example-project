<?php

namespace App\Test\Integration\Authentication;

use App\Domain\Security\Enum\SecurityType;
use App\Domain\Security\Exception\SecurityException;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

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
        ], new UserFixture());

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

        // Simulate amount of login requests to go through for every throttling level
        // $nthLoginRequest is the current nth amount of login request that is made (4th, 5th, 6th etc.)
        for ($nthLoginRequest = 1; $nthLoginRequest <= $thresholdForStrictestThrottle; $nthLoginRequest++) {
            // * Until the lowest threshold is reached, the login requests are normal and should not be throttled
            if ($nthLoginRequest < $lowestThreshold) {
                // As long as the nth request is below first threshold no exception is thrown but authentication_log
                // entry is added which is needed to test throttling in the next iterations
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
                        // Creates an additional authentication_log entry that is needed to assert for next iterations
                        // with higher throttling
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

                        // ? Test that user can make a new request that is processed after having waited the delay
                        // Below this request after waiting the delay is with WRONG credentials
                        if (is_numeric($delay)) {
                            // After waiting the delay, user is allowed to make new login request but if the credentials
                            // are wrong again (using same $request), an exception is expected from the **second
                            // security check** after the failed request in LoginVerifier.
                            // (This second security check is not done if request has correct credentials)
                            // Prepone last authentication_log to simulate waiting
                            $this->preponeLastAuthenticationLogEntry((int)$delay);
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
                                // authentication_log, and the login request summary would be falsified in the next
                                // iterations/tests
                                $this->deleteLastAuthenticationLog();
                            }

                            // * Assert that after waiting the delay, a successful request can be made with correct credentials
                            $requestAfterWaitingDelay = $this->createFormRequest(
                                'POST',
                                $this->urlFor('login-submit'),
                                $correctLoginRequestBody
                            );
                            $responseAfterWaiting = $this->app->handle($requestAfterWaitingDelay);
                            // Again delete the most recent authentication log entry as the request above is also an
                            // "aside" test that should not influence login stats of the next iterations.
                            $this->deleteLastAuthenticationLog();

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
                            // done before the new `authentication_log` entry is made.
                            // $this->postponeLastAuthenticationLog($delay);
                            // Edit: the above is not needed.
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
    private function preponeLastAuthenticationLogEntry(int $seconds): void
    {
        // Change row with the highest id
        $query = "UPDATE authentication_log SET created_at = DATE_SUB(created_at, INTERVAL $seconds SECOND) ORDER BY id DESC LIMIT 1";
        $this->createQueryStatement($query);
    }

    /**
     * Delete most recent request.
     *
     * @return void
     */
    private function deleteLastAuthenticationLog(): void
    {
        // Change row with the highest id
        $query = 'DELETE FROM authentication_log ORDER BY id DESC LIMIT 1';
        $this->createQueryStatement($query);
    }
}
