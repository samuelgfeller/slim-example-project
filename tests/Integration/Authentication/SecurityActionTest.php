<?php

namespace App\Test\Integration\Authentication;

use App\Domain\Security\Exception\SecurityException;
use App\Infrastructure\Security\RequestPreponerRepository;
use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\UserFixture;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * In this class actions that are processed by the `SecurityService` are tested
 */
class SecurityActionTest extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait;
    use RouteTestTrait;

    /**
     * Test thresholds and according delays once with failed logins and once with successes
     * If login request amount exceeds threshold, the user has to wait a certain delay
     * This is still far below a global test
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::loginUserProvider()
     *
     * @param array $loginFormValues One dataset with wrong credentials and one with correct ones
     */
    public function testTooManyLoginAttempts(array $loginFormValues): void
    {
        // Fixture not needed to insert successful login requests to make sure that request doesn't fail because global
        // ratio too low as there is a minimal hard limit for the global check to fail

        // Insert user for the test of successful login abuse (provided via dataProvider)
        $this->insertFixtures([UserFixture::class]);

        $throttleArr = $this->container->get('settings')['security']['login_throttle'];

        // Failed request
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $loginFormValues);

        // Needed to prepone request date to simulate waiting delay
        $requestPreponerRepository = $this->container->get(RequestPreponerRepository::class);

        $lowestThreshold = array_key_first($throttleArr);
        // It should be tested with the most strict throttle as well. This means that last run should exceed last threshold
        $amountForStrictestThrottle = array_key_last($throttleArr) + 1;

        // $i > $delay would always match the delay after the first threshold and thus ignore all other thresholds
        // Reversing throttleArr will check for the big delays first
        krsort($throttleArr);
        // highestThreshold + 1 because it should be tested against
        for ($i = 1; $i <= $amountForStrictestThrottle; $i++) {
            // Inside loop are if statements with right values
            foreach ($throttleArr as $threshold => $delay) {
                // Below and on the first threshold no exception is thrown
                if (($threshold === $lowestThreshold) && $i <= $lowestThreshold) {
                    $this->app->handle($request);
                    break; // leave foreach loop
                }

                // Request amount beyond first threshold therefore experiencing throttle
                if ($i > $threshold) {
                    // captcha expected for the last threshold
                    try {
                        $this->app->handle($request);
                        self::fail('SecurityException should be thrown');
                    } catch (SecurityException $se) {
                        self::assertEqualsWithDelta($delay, $se->getRemainingDelay(), 1);
                        self::assertSame(SecurityException::USER_LOGIN, $se->getType());
                    }
                    // Highest throttle is probably captcha not a numeric delay that could be preponed
                    if (!($i >= $amountForStrictestThrottle)) {
                        // Reset to time to simulate waiting
                        $requestPreponerRepository->preponeLastRequest($delay);
                        // After waiting the delay, user is allowed to make new login request
                        $responseAfterWaiting = $this->app->handle($request);
                        // Now it could be asserted that response an either successful or failed login
                        // SecurityException will not be thrown after invalid login as check happens in beginning of next request
                    }
                    break; // leave foreach loop
                }
            }
        }
        ksort($throttleArr);

        // The above loop roughly has the same checks as the follows
/*        for ($i = 1; $i <= 12; $i++) {
            // SecurityCheck is done in beginning of next request so if threshold is 4, check that'll fail will be done
            // in the 5th request
            if ($i <= 4) {
                // no exception expected
                $this->app->handle($request);
            } // First the highest threshold otherwise lowest would always match and it'd never go in the next ones
            elseif ($i > 12) {// first throttle expected so 10s (after 4 failures)
                try {
                    $this->app->handle($request);
                    self::fail('SecurityException should be thrown');
                } catch (SecurityException $se) {
                    self::assertSame('captcha', $se->getRemainingDelay());
                    self::assertSame(SecurityException::USER_LOGIN, $se->getType());
                }
            } // If threshold is 12, check that'll fail will be done in the 13th request
            elseif ($i > 9) {// second throttle expected so 120s
                try {
                    $this->app->handle($request);
                    self::fail('SecurityException should be thrown');
                } catch (SecurityException $se) {
                    self::assertEqualsWithDelta(120, $se->getRemainingDelay(), 1);
                    self::assertSame(SecurityException::USER_LOGIN, $se->getType());
                    // Reset to time to simulate waiting
                    $requestPreponerRepository->preponeLastRequest(120);
                }

                // After waiting the delay, user is allowed to make new login request
                $responseAfterWaiting = $this->app->handle($request);
                // Assert that request was a login request with invalid credentials
                self::assertSame(401, $responseAfterWaiting->getStatusCode());
                // SecurityException will not be thrown after invalid login as check happens in beginning of next request
            } elseif ($i > 4) {// captcha expected
                try {
                    $response = $this->app->handle($request);
                    self::fail('SecurityException should be thrown');
                } catch (SecurityException $se) {
                    self::assertEqualsWithDelta(10, $se->getRemainingDelay(), 1);
                    self::assertSame(SecurityException::USER_LOGIN, $se->getType());
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
