<?php

namespace App\Test\Provider\Security;

use App\Domain\Security\Data\RequestStatsData;

class UserRequestProvider
{
    // Placed on top to easily change.
    // ! This should be the same as the config values $settings['security']['login_throttle_rule']
    // ? Example values as I can't take the values from settings because I can't access container in provider
    // (Error: Typed property $container must not be accessed before initialization)
    // Change provider return values too if different from 3
    private array $userLoginThrottle = [4 => 10, 9 => 120, 12 => 'captcha'];

    // Only request limit, not delay
    // ! This should be the same as the config values $settings['security']['user_email_throttle_rule']
    // Change provider return values too if different from 3
    private array $userEmailRequestThrottle = [5 => 2, 10 => 4, 20 => 'captcha'];

    // ! This should be the same as the config values $settings['security']['global_daily_email_threshold']
    private int $globalDailyEmailThreshold = 300;
    // ! And  $settings['security']['global_monthly_email_threshold']
    private int $globalMonthlyEmailThreshold = 1000;

    /**
     * Get request stats array populated with a specific amount
     * For global or user as $requestAmount is passed as argument.
     *
     * @param int|string $requestAmount
     * @param string $type
     *
     * @return RequestStatsData
     */
    private function stats(int|string $requestAmount, string $type): RequestStatsData
    {
        if ($type === 'email') {
            return new RequestStatsData([
                'request_amount' => $requestAmount,
                'sent_emails' => $requestAmount,
                'login_failures' => 0,
                'login_successes' => 0,
            ]);
        }
        // To test that exception is thrown for failure but also for success, they have to be tested each
        if ($type === 'loginF') {
            return new RequestStatsData([
                'request_amount' => $requestAmount,
                'sent_emails' => 0,
                'login_failures' => $requestAmount,
                'login_successes' => 0,
            ]);
        }
        if ($type === 'loginS') {
            return new RequestStatsData([
                // One is added as
                'request_amount' => $requestAmount,
                'sent_emails' => 0,
                'login_failures' => 0,
                'login_successes' => $requestAmount,
            ]);
        }

        return new RequestStatsData();
    }

    /**
     * Provides all login request amounts in each different threshold where an exception must be thrown
     *  - Too many login failures in each threshold from same ip or specific user
     *  - Too many login success requests (also for each threshold) from same ip or specific user.
     *
     * @return array[]
     */
    public function individualLoginThrottlingTestCases(): array
    {
        // Values for logins (L)
        [$firstL, $secondL, $thirdL] = array_keys($this->userLoginThrottle);
        [$firstDelayL, $secondDelayL, $thirdDelayL] = array_values($this->userLoginThrottle);

        return [
            // ! LOGIN FAILURE VALUES
            // ? First three are to test ip request stats
            // Failed or successful login requests coming from the same ip. Throttled same as rapid fire on user.
            [
                // request limit not needed as it's expected that error is thrown and that only happens if limit reached
                'delay' => $firstDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats($firstL, 'loginF'),
                    'email_stats' => $this->stats(0, 'loginF'),
                ],
            ],
            [
                'delay' => $secondDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats($secondL, 'loginF'),
                    'email_stats' => $this->stats(0, 'loginF'),
                ],
            ],
            [
                'delay' => $thirdDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats($thirdL, 'loginF'),
                    'email_stats' => $this->stats(0, 'loginF'),
                ],
            ],

            // ? Next are to test login requests made on one user
            [
                'delay' => $firstDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats(0, 'loginF'),
                    'email_stats' => $this->stats($firstL, 'loginF'),
                ],
            ],
            [
                'delay' => $secondDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats(0, 'loginF'),
                    'email_stats' => $this->stats($secondL, 'loginF'),
                ],
            ],
            [
                'delay' => $thirdDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats(0, 'loginF'),
                    'email_stats' => $this->stats($thirdL, 'loginF'),
                ],
            ],
            // ! LOGIN SUCCESS VALUES
            // ? First three are to test ip request stats
            [
                // request limit not needed as it's expected that error is thrown and that only happens if limit reached
                'delay' => $firstDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats($firstL, 'loginS'),
                    'email_stats' => $this->stats(0, 'loginS'),
                ],
            ],
            [
                'delay' => $secondDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats($secondL, 'loginS'),
                    'email_stats' => $this->stats(0, 'loginS'),
                ],
            ],
            [
                'delay' => $thirdDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats($thirdL, 'loginS'),
                    'email_stats' => $this->stats(0, 'loginS'),
                ],
            ],

            // ? Next are to test login requests made on one user
            [
                'delay' => $firstDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats(0, 'loginS'),
                    'email_stats' => $this->stats($firstL, 'loginS'),
                ],
            ],
            [
                'delay' => $secondDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats(0, 'loginS'),
                    'email_stats' => $this->stats($secondL, 'loginS'),
                ],
            ],
            [
                'delay' => $thirdDelayL,
                'request_stats' => [
                    'ip_stats' => $this->stats(0, 'loginS'),
                    'email_stats' => $this->stats($thirdL, 'loginS'),
                ],
            ],
        ];
    }

    /**
     * Provides values for email abuse test concerning specific email or coming from ip.
     * Content are requests that exceed each email send limitation.
     *
     * @return array[]
     */
    public function individualEmailThrottlingTestCases(): array
    {
        // Values for emails (E)
        [$firstE, $secondE, $thirdE] = array_keys($this->userEmailRequestThrottle);
        [$firstDelayE, $secondDelayE, $thirdDelayE] = array_values($this->userEmailRequestThrottle);

        // ? First three are to test ip request stats
        // All thresholds for email requests coming from the same ip. Throttled same as rapid fire on user.
        return [
            [
                // request limit not needed as it's expected that error is thrown and that only happens if limit reached
                'delay' => $firstDelayE,
                'request_stats' => [
                    'ip_stats' => $this->stats($firstE, 'email'),
                    'email_stats' => $this->stats(0, 'email'),
                ],
            ],
            [
                'delay' => $secondDelayE,
                'request_stats' => [
                    'ip_stats' => $this->stats($secondE, 'email'),
                    'email_stats' => $this->stats(0, 'email'),
                ],
            ],
            [
                'delay' => $thirdDelayE,
                'request_stats' => [
                    'ip_stats' => $this->stats($thirdE, 'email'),
                    'email_stats' => $this->stats(0, 'email'),
                ],
            ],

            // ? Next are to test email requests made on one user
            [
                'delay' => $firstDelayE,
                'request_stats' => [
                    'ip_stats' => $this->stats(0, 'email'),
                    'email_stats' => $this->stats($firstE, 'email'),
                ],
            ],
            [
                'delay' => $secondDelayE,
                'request_stats' => [
                    'ip_stats' => $this->stats(0, 'email'),
                    'email_stats' => $this->stats($secondE, 'email'),
                ],
            ],
            [
                'delay' => $thirdDelayE,
                'request_stats' => [
                    'ip_stats' => $this->stats(0, 'email'),
                    'email_stats' => $this->stats($thirdE, 'email'),
                ],
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
    public function globalEmailStatsProvider(): array
    {
        return [
            [
                // Values same as threshold as exception is thrown if it equals or is greater than threshold
                // string cake query builder also returns string
                'daily_email_amount' => $this->globalDailyEmailThreshold,
                // Daily amount given here as it wouldn't make sense to have X amount in a day but 0 in last month
                'monthly_email_amount' => $this->globalDailyEmailThreshold, // At least same as daily amount
            ],
            [
                'daily_email_amount' => 0,
                'monthly_email_amount' => $this->globalMonthlyEmailThreshold,
            ],
        ];
    }
}
