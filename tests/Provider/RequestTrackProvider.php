<?php

namespace App\Test\Provider;

use App\Domain\Security\SecurityException;

class RequestTrackProvider
{
    // Placed on top to easily change.
    // ! This should be the same than the config values $settings['security']['login_throttle']
    // ? Example values as I can't take the values from settings because I can't access container in provider
    // (Error: Typed property $container must not be accessed before initialization)
    // Change provider return values too if different than 3
    private array $userLoginThrottle = [4 => 10, 9 => 120, 12 => 'captcha'];

    // Only request limit, not delay
    // ! This should be the same than the config values $settings['security']['user_email_throttle']
    // Change provider return values too if different than 3
    private array $userEmailRequestThrottle = [5 => 2, 10 => 4, 20 => 'captcha'];

    // ! This should be the same than the config values $settings['security']['global_daily_email_threshold']
    private int $globalDailyEmailThreshold = 300;
    // ! And  $settings['security']['global_monthly_email_threshold']
    private int $globalMonthlyEmailThreshold = 1000;


    /**
     * Get request stats array populated with a specific amount
     * For global or user as $requestAmount is passed as argument
     *
     * @param int|string $requestAmount
     * @param string $type
     * @return array
     */
    public function stats(int|string $requestAmount, string $type): array
    {
        if ($type === 'email') {
            return [
                'request_amount' => $requestAmount,
                'sent_emails' => $requestAmount,
                'login_failures' => 0,
                'login_successes' => 0,
            ];
        }
        // To test that exception is thrown for failure but also for success, they have to be tested each
        if ($type === 'loginF') {
            return [
                'request_amount' => $requestAmount,
                'sent_emails' => 0,
                'login_failures' => $requestAmount,
                'login_successes' => 0,
            ];
        }
        if ($type === 'loginS') {
            return [
                'request_amount' => $requestAmount,
                'sent_emails' => 0,
                'login_failures' => 0,
                'login_successes' => $requestAmount,
            ];
        }

        return [];
    }

    /**
     * Provides all login request amounts in each different threshold where an exception must be thrown AND
     * requests that exceed email sent limitation to avoid email abuse
     *
     * In the same provider to have only 1 test method
     *
     * @return array[]
     */
    public function userLoginAndEmailProvider(): array
    {
        // Values for logins (L)
        [$firstL, $secondL, $thirdL] = array_keys($this->userLoginThrottle);
        [$firstDelayL, $secondDelayL, $thirdDelayL] = array_values($this->userLoginThrottle);
        // Values for emails (E)
        [$firstE, $secondE, $thirdE] = array_keys($this->userEmailRequestThrottle);
        [$firstDelayE, $secondDelayE, $thirdDelayE] = array_values($this->userEmailRequestThrottle);

        // Example values as I can't take the values from settings.php because I can't access container in provider
        // (Error: Typed property $container must not be accessed before initialization)
        return [
            // ! LOGIN FAILURE VALUES
            // ? First three are to test ip request stats
            // Failed or successful login requests coming from the same ip. Throttled same as rapid fire on user.
            [
                // request limit not needed as it's expected that error is thrown and that only happens if limit reached
                'delay' => $firstDelayL,
                'global_request_stats' => $this->stats(0, 'loginF'),
                'ip_request_stats' => $this->stats($firstL, 'loginF'),
                'user_request_stats' => $this->stats(0, 'loginF'),
                'type' => SecurityException::USER_LOGIN,
            ],
            [
                'delay' => $secondDelayL,
                'global_request_stats' => $this->stats(0, 'loginF'),
                'ip_request_stats' => $this->stats($secondL, 'loginF'),
                'user_request_stats' => $this->stats(0, 'loginF'),
                'type' => SecurityException::USER_LOGIN,
            ],
            [
                'delay' => $thirdDelayL,
                'global_request_stats' => $this->stats(0, 'loginF'),
                'ip_request_stats' => $this->stats($thirdL, 'loginF'),
                'user_request_stats' => $this->stats(0, 'loginF'),
                'type' => SecurityException::USER_LOGIN,
            ],

            // ? Next are to test login requests made on one user
            [
                'delay' => $firstDelayL,
                'global_request_stats' => $this->stats(0, 'loginF'),
                'ip_request_stats' => $this->stats(0, 'loginF'),
                'user_request_stats' => $this->stats($firstL, 'loginF'),
                'type' => SecurityException::USER_LOGIN,
            ],
            [
                'delay' => $secondDelayL,
                'global_request_stats' => $this->stats(0, 'loginF'),
                'ip_request_stats' => $this->stats(0, 'loginF'),
                'user_request_stats' => $this->stats($secondL, 'loginF'),
                'type' => SecurityException::USER_LOGIN,
            ],
            [
                'delay' => $thirdDelayL,
                'global_request_stats' => $this->stats(0, 'loginF'),
                'ip_request_stats' => $this->stats(0, 'loginF'),
                'user_request_stats' => $this->stats($thirdL, 'loginF'),
                'type' => SecurityException::USER_LOGIN,
            ],
            // ! LOGIN SUCCESS VALUES
            // ? First three are to test ip request stats
            [
                // request limit not needed as it's expected that error is thrown and that only happens if limit reached
                'delay' => $firstDelayL,
                'global_request_stats' => $this->stats(0, 'loginS'),
                'ip_request_stats' => $this->stats($firstL, 'loginS'),
                'user_request_stats' => $this->stats(0, 'loginS'),
                'type' => SecurityException::USER_LOGIN,
            ],
            [
                'delay' => $secondDelayL,
                'global_request_stats' => $this->stats(0, 'loginS'),
                'ip_request_stats' => $this->stats($secondL, 'loginS'),
                'user_request_stats' => $this->stats(0, 'loginS'),
                'type' => SecurityException::USER_LOGIN,
            ],
            [
                'delay' => $thirdDelayL,
                'global_request_stats' => $this->stats(0, 'loginS'),
                'ip_request_stats' => $this->stats($thirdL, 'loginS'),
                'user_request_stats' => $this->stats(0, 'loginS'),
                'type' => SecurityException::USER_LOGIN,
            ],

            // ? Next are to test login requests made on one user
            [
                'delay' => $firstDelayL,
                'global_request_stats' => $this->stats(0, 'loginS'),
                'ip_request_stats' => $this->stats(0, 'loginS'),
                'user_request_stats' => $this->stats($firstL, 'loginS'),
                'type' => SecurityException::USER_LOGIN,
            ],
            [
                'delay' => $secondDelayL,
                'global_request_stats' => $this->stats(0, 'loginS'),
                'ip_request_stats' => $this->stats(0, 'loginS'),
                'user_request_stats' => $this->stats($secondL, 'loginS'),
                'type' => SecurityException::USER_LOGIN,
            ],
            [
                'delay' => $thirdDelayL,
                'global_request_stats' => $this->stats(0, 'loginS'),
                'ip_request_stats' => $this->stats(0, 'loginS'),
                'user_request_stats' => $this->stats($thirdL, 'loginS'),
                'type' => SecurityException::USER_LOGIN,
            ],

            // ! EMAIL VALUES
            // ? First three are to test ip request stats
            // All thresholds for email requests coming from the same ip. Throttled same as rapid fire on user.
            [
                // request limit not needed as it's expected that error is thrown and that only happens if limit reached
                'delay' => $firstDelayE,
                'global_request_stats' => $this->stats(0, 'email'),
                'ip_request_stats' => $this->stats($firstE, 'email'),
                'user_request_stats' => $this->stats(0, 'email'),
                'type' => SecurityException::USER_EMAIL,
            ],
            [
                'delay' => $secondDelayE,
                'global_request_stats' => $this->stats(0, 'email'),
                'ip_request_stats' => $this->stats($secondE, 'email'),
                'user_request_stats' => $this->stats(0, 'email'),
                'type' => SecurityException::USER_EMAIL,
            ],
            [
                'delay' => $thirdDelayE,
                'global_request_stats' => $this->stats(0, 'email'),
                'ip_request_stats' => $this->stats($thirdE, 'email'),
                'user_request_stats' => $this->stats(0, 'email'),
                'type' => SecurityException::USER_EMAIL,
            ],

            // ? Next are to test email requests made on one user
            [
                'delay' => $firstDelayE,
                'global_request_stats' => $this->stats(0, 'email'),
                'ip_request_stats' => $this->stats(0, 'email'),
                'user_request_stats' => $this->stats($firstE, 'email'),
                'type' => SecurityException::USER_EMAIL,
            ],
            [
                'delay' => $secondDelayE,
                'global_request_stats' => $this->stats(0, 'email'),
                'ip_request_stats' => $this->stats(0, 'email'),
                'user_request_stats' => $this->stats($secondE, 'email'),
                'type' => SecurityException::USER_EMAIL,
            ],
            [
                'delay' => $thirdDelayE,
                'global_request_stats' => $this->stats(0, 'email'),
                'ip_request_stats' => $this->stats(0, 'email'),
                'user_request_stats' => $this->stats($thirdE, 'email'),
                'type' => SecurityException::USER_EMAIL,
            ],
        ];
    }

    /**
     * Provides values for global emails abuse test
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
                'daily_email_amount' => (string)$this->globalDailyEmailThreshold,
                // Daily amount given here as it wouldn't make sense to have X amount in a day but 0 in last month
                'monthly_email_amount' => (string)$this->globalDailyEmailThreshold, // At least same as daily amount
            ],
            [
                'daily_email_amount' => '0',
                'monthly_email_amount' => (string)$this->globalMonthlyEmailThreshold,
            ],
        ];
    }
}