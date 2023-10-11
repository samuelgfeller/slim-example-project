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
     * Get request stats array populated with a specific amount
     * For global or user as $requestAmount is passed as argument.
     *
     * @param int|string $requestAmount
     * @param string $type
     *
     * @return array{
     *     logins_by_email: array{successes: int, failures: int},
     *     logins_by_ip: array{successes: int, failures: int},
     * }
     */
    private static function summary(int|string $requestAmount, string $type): array
    {
        if ($type === 'email') {
            return [ // todo change when email is refactored
                'request_amount' => $requestAmount,
                'sent_emails' => $requestAmount,
                'login_failures' => 0,
                'login_successes' => 0,
            ];
        }
        // To test that exception is thrown for failure but also for success, they have to be tested each
        if ($type === 'loginF') {
            return [
                'logins_by_email' => ['successes' => 0, 'failures' => $requestAmount],
                'logins_by_ip' => ['successes' => 0, 'failures' => $requestAmount]
            ];
        }
        if ($type === 'loginS') {
            return [
                'logins_by_email' => ['successes' => $requestAmount, 'failures' => 0],
                'logins_by_ip' => ['successes' => $requestAmount, 'failures' => 0]
            ];
        }
        return [];
    }

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
                    'logins_by_ip' => ['successes' => 0, 'failures' => $firstL]
                ],

                [
                    'delay' => $secondDelayL,
                    'log_summary' => [
                        'logins_by_email' => ['successes' => 0, 'failures' => 0],
                        'logins_by_ip' => ['successes' => 0, 'failures' => $secondL]
                    ],
                ],
                [
                    'delay' => $thirdDelayL,
                    'log_summary' => [
                        'logins_by_email' => ['successes' => 0, 'failures' => 0],
                        'logins_by_ip' => ['successes' => 0, 'failures' => $thirdL]
                    ],
                ],
                // ? Next are to test login requests made on one user account
                [
                    // logins by email test
                    'delay' => $firstDelayL,
                    'log_summary' => [
                        'logins_by_email' => ['successes' => 0, 'failures' => $firstL],
                        'logins_by_ip' => ['successes' => 0, 'failures' => 0]
                    ],
                ],
                [
                    'delay' => $secondDelayL,
                    'log_summary' => [
                        'logins_by_email' => ['successes' => 0, 'failures' => $secondL],
                        'logins_by_ip' => ['successes' => 0, 'failures' => 0]
                    ],
                ],
                [
                    'delay' => $thirdDelayL,
                    'log_summary' => [
                        'logins_by_email' => ['successes' => 0, 'failures' => $thirdL],
                        'logins_by_ip' => ['successes' => 0, 'failures' => 0]
                    ],
                ],
            ],
            // ! LOGIN SUCCESS VALUES (throttle on too many successful login requests)
            // ? First three are to test ip request stats
            [
                // request limit not needed as it's expected that error is thrown and that only happens if limit reached
                'delay' => $firstDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => $firstL, 'failures' => 0]
                ],
            ],
            [
                'delay' => $secondDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => $secondL, 'failures' => 0]
                ],
            ],
            [
                'delay' => $thirdDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => $thirdL, 'failures' => 0]
                ],
            ],

            // ? Next are to test login requests made on one user
            [
                'delay' => $firstDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => $firstL, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0]
                ],
            ],
            [
                'delay' => $secondDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => $secondL, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0]
                ],
            ],
            [
                'delay' => $thirdDelayL,
                'log_summary' => [
                    'logins_by_email' => ['successes' => $thirdL, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0]
                ],
            ],
        ];
    }
}
