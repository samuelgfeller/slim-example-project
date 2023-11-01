<?php

namespace App\Test\Provider\Security;

class LoginRequestProvider
{
    // Placed on top to easily change.
    // ! This should be the same as the config values $settings['security']['login_throttle_rule'] for integration tests
    // ? Example values as I can't take the values from settings because I can't access container in provider
    // (Error: Typed property $container must not be accessed before initialization)
    // Change provider return values too if different from 3
    private const userLoginThrottle = [4 => 10, 9 => 120, 12 => 'captcha'];

    /**
     * Provides all login request amounts in each different threshold where an exception must be thrown
     *  - Too many login failures in each threshold from same ip or specific user
     *  - Too many login success requests (also for each threshold) from same ip or specific user.
     *
     * @return array[]
     */
    public static function individualLoginThrottlingTestCases(): array
    {
        // Values for logins (L)
        [$firstL, $secondL, $thirdL] = array_keys(self::userLoginThrottle);
        [$firstDelayL, $secondDelayL, $thirdDelayL] = array_values(self::userLoginThrottle);

        return [
            // ! LOGIN FAILURE VALUES
            // ? First three are to test ip request stats
            // Failed or successful login requests coming from the same ip. Throttled same as rapid fire on user.
            [
                // ip test
                'delay' => $firstDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => $firstL],
                ],
            ],
            [
                'delay' => $secondDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => $secondL],
                ],
            ],
            [
                'delay' => $thirdDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => $thirdL],
                ],
            ],
            // ? Next are to test login requests made on one user account
            [
                // logins by email test
                'delay' => $firstDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => $firstL],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0],
                ],
            ],
            [
                'delay' => $secondDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => $secondL],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0],
                ],
            ],
            [
                'delay' => $thirdDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => $thirdL],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0],
                ],
            ],
        ];
    }
}
