<?php

namespace App\Test\TestCase\Security\Provider;

class LoginRequestProvider
{
    private const securitySettings = [
        'throttle_login' => true,
        'login_throttle_rule' => [4 => 10, 9 => 120, 12 => 'captcha'],
        'timespan' => 3600,
    ];

    /**
     * Provides all login request amounts for each different threshold where an exception must be thrown.
     *  - Too many login failures in each threshold from the same ip or specific user
     *  - Too many login success requests (also for each threshold) from the same ip or specific user.
     *
     * @return array[]
     */
    public static function individualLoginThrottlingTestCases(): array
    {
        // Values for logins (L)
        [$firstL, $secondL, $thirdL] = array_keys(self::securitySettings['login_throttle_rule']);
        [$firstDelayL, $secondDelayL, $thirdDelayL] = array_values(self::securitySettings['login_throttle_rule']);

        return [
            // ! LOGIN FAILURES
            // ? Login requests coming from the same ip address
            // Failed or successful login requests coming from the same ip.
            [
                // ip test
                'delay' => $firstDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => $firstL],
                ],
                'securitySettings' => self::securitySettings,
            ],
            [
                'delay' => $secondDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => $secondL],
                ],
                'securitySettings' => self::securitySettings,
            ],
            [
                'delay' => $thirdDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => $thirdL],
                ],
                'securitySettings' => self::securitySettings,
            ],
            // ? Login requests made on one user account
            [
                // logins by email test
                'delay' => $firstDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => $firstL],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0],
                ],
                'securitySettings' => self::securitySettings,
            ],
            [
                'delay' => $secondDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => $secondL],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0],
                ],
                'securitySettings' => self::securitySettings,
            ],
            [
                'delay' => $thirdDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => $thirdL],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0],
                ],
                'securitySettings' => self::securitySettings,
            ],

            // ! LOGIN SUCCESSES
            // ? Login requests coming from the same ip address
            // Successful login requests coming from the same ip.
            [
                // ip test
                'delay' => $firstDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => $firstL, 'failures' => 0],
                ],
                'securitySettings' => self::securitySettings,
            ],
            [
                'delay' => $secondDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => $secondL, 'failures' => 0],
                ],
                'securitySettings' => self::securitySettings,
            ],
            [
                'delay' => $thirdDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => 0, 'failures' => 0],
                    'logins_by_ip' => ['successes' => $thirdL, 'failures' => 0],
                ],
                'securitySettings' => self::securitySettings,
            ],
            // ? Login requests made on one user account
            [
                // logins by email test
                'delay' => $firstDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => $firstL, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0],
                ],
                'securitySettings' => self::securitySettings,
            ],
            [
                'delay' => $secondDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => $secondL, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0],
                ],
                'securitySettings' => self::securitySettings,
            ],
            [
                'delay' => $thirdDelayL,
                'ipAndEmailLogSummary' => [
                    'logins_by_email' => ['successes' => $thirdL, 'failures' => 0],
                    'logins_by_ip' => ['successes' => 0, 'failures' => 0],
                ],
                'securitySettings' => self::securitySettings,
            ],
        ];
    }
}
