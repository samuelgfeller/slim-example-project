<?php

namespace App\Test\Provider\Security;

class EmailRequestProvider
{
    // ! This should be the same as the config values $settings['security']['user_email_throttle_rule'] for integration tests
    // ? Example values as I can't take the values from settings because I can't access container in provider

    // Change provider return values too if different from 3
    private const emailRequestThrottlingSetting = [5 => 2, 10 => 4, 20 => 'captcha'];

    // ! This should be the same as the config values $settings['security']['global_daily_email_threshold']
    private const globalDailyEmailThreshold = 300;
    // ! And  $settings['security']['global_monthly_email_threshold']
    private const globalMonthlyEmailThreshold = 1000;


    /**
     * Provides values for email abuse test concerning specific email or coming from ip.
     * Content are requests that exceed each email send limitation.
     *
     * @return array[]
     */
    public static function individualEmailThrottlingTestCases(): array
    {
        // Email thresholds for the throttling to take place
        [$firstThreshold, $secondThreshold, $thirdThreshold] = array_keys(self::emailRequestThrottlingSetting);
        [$firstDelay, $secondDelay, $thirdDelay] = array_values(self::emailRequestThrottlingSetting);

        return [
            [
                'delay' => $firstDelay,
                // Email security check should fail if threshold is reached
                'email_amount_in_timespan' => $firstThreshold,
            ],
            [
                'delay' => $secondDelay,
                'email_amount_in_timespan' => $secondThreshold,
            ],
            [
                'delay' => $thirdDelay,
                'email_amount_in_timespan' => $thirdThreshold,
            ],
        ];
    }

    /**
     * Provides values for global emails abuse test.
     *
     * The first time the provider sets the daily amount and leaves the monthly blank
     * The second time this test is executed the provider sets monthly amount and lefts daily blank
     *
     * Values same as threshold as exception is thrown if it equals or is greater than threshold
     *
     * @return array[]
     */
    public static function globalEmailStatsProvider(): array
    {
        return [
            [
                // Values same as threshold as exception is thrown if it equals or is greater than threshold
                // string cake query builder also returns string
                'daily_email_amount' => self::globalDailyEmailThreshold,
                // Daily amount given here as it wouldn't make sense to have X amount in a day but 0 in last month
                'monthly_email_amount' => self::globalDailyEmailThreshold, // At least same as daily amount
            ],
            [
                'daily_email_amount' => 0,
                'monthly_email_amount' => self::globalMonthlyEmailThreshold,
            ],
        ];
    }
}
