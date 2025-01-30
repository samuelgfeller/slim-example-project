<?php

namespace App\Test\TestCase\Security\Provider;

class EmailRequestProvider
{
    // Settings.php is mocked with these config values in the unit test case
    private const securitySettings = [
        'throttle_email' => true,
        'timespan' => 3600,
        'user_email_throttle_rule' => [5 => 2, 10 => 4, 20 => 'captcha'],
        'global_daily_email_threshold' => 300,
        'global_monthly_email_threshold' => 900,
    ];

    /**
     * Provides values for email abuse test concerning specific email or coming from ip.
     * Content are requests that exceed each email send limitation.
     *
     * @return array[]
     */
    public static function individualEmailThrottlingTestCases(): array
    {
        // Email thresholds for the throttling to take place
        [$firstThreshold, $secondThreshold, $thirdThreshold] = array_keys(
            self::securitySettings['user_email_throttle_rule']
        );
        [$firstDelay, $secondDelay, $thirdDelay] = array_values(self::securitySettings['user_email_throttle_rule']);

        return [
            [
                'delay' => $firstDelay,
                // Email security check should fail if the threshold is reached
                'emailLogAmountInTimeSpan' => $firstThreshold,
                'securitySettings' => self::securitySettings,
            ],
            [
                'delay' => $secondDelay,
                'emailLogAmountInTimeSpan' => $secondThreshold,
                'securitySettings' => self::securitySettings,
            ],
            [
                'delay' => $thirdDelay,
                'emailLogAmountInTimeSpan' => $thirdThreshold,
                'securitySettings' => self::securitySettings,
            ],
        ];
    }

    /**
     * Provides values for global emails abuse test.
     *
     * The first time the provider sets the amount for today to test the daily limit.
     * For the second iteration, the provider returns email amount from this month to test monthly limit.
     *
     * The values are the same as the thresholds as the exception is thrown if it equals or is greater than the threshold.
     *
     * @return array[]
     */
    public static function globalEmailStatsProvider(): array
    {
        return [
            // Daily threshold test
            [
                // The values are the same as threshold as exception is thrown if it equals or is greater than threshold
                'todayEmailAmount' => self::securitySettings['global_daily_email_threshold'],
                // The amount for this month has to be at least the same as from today
                'thisMonthEmailAmount' => self::securitySettings['global_daily_email_threshold'],
                'securitySettings' => self::securitySettings,
            ],
            // Monthly threshold test
            [
                'todayEmailAmount' => 0,
                'thisMonthEmailAmount' => self::securitySettings['global_monthly_email_threshold'],
                'securitySettings' => self::securitySettings,
            ],
        ];
    }
}
