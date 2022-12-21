<?php

namespace App\Domain\Security\Service;

use App\Domain\Security\Data\RequestStatsData;
use App\Domain\Security\Enum\SecurityType;
use App\Domain\Security\Exception\SecurityException;
use App\Domain\Settings;
use App\Infrastructure\Security\LoginRequestFinderRepository;

class SecurityLoginChecker
{
    private array $securitySettings;

    public function __construct(
        private readonly SecurityCaptchaVerifier $captchaVerifier,
        private readonly LoginRequestFinder $loginRequestFinder,
        private readonly LoginRequestFinderRepository $loginRequestFinderRepository,
        Settings $settings
    ) {
        $this->securitySettings = $settings->get('security');
    }

    /**
     * Threats:
     * - Rapid fire attacks (when bots try to log in with 1000 different passwords on one user account)
     * - Distributed brute force attacks (try to log in 1000 different users with most common password).
     *
     * Perform security check for login requests:
     * - coming from the same ip address
     * - concerning a specific user account
     * - global login requests (throttle after x percent of login failures)
     *
     * Throttle behaviour: Limit log in attempts per user
     * - After x amount of login requests or sent emails in an hour, user has to wait a certain delay before trying again
     * - For each next login request or next email sent in the same hour, the user has to wait the same delay
     * - Until it eventually increases after value y
     * - If login or email requests continue, at amount z captcha is required from the user
     * - This rule applies to login requests on a specific user or login requests coming from a specific ip
     *
     * @param string $email
     * @param string|null $reCaptchaResponse
     */
    public function performLoginSecurityCheck(string $email, string|null $reCaptchaResponse = null): void
    {
        if ($this->securitySettings['throttle_login'] === true) {
            // reCAPTCHA verification
            $validCaptcha = false;
            if ($reCaptchaResponse !== null) {
                $validCaptcha = $this->captchaVerifier->verifyReCaptcha(
                    $reCaptchaResponse,
                    SecurityType::USER_LOGIN
                );
            }
            // If captcha is valid the other security checks don't have to be made
            if ($validCaptcha !== true) {
                // Most strict. Very low limit on failed requests for specific email or coming from an ip
                $stats = $this->loginRequestFinder->findLoginStats($email);
                $this->performLoginCheck($stats['ip_stats'], $stats['email_stats'], $email);
                // Global login check
                $this->performGlobalLoginCheck();
            }
        }
    }

    /**
     * Check that login requests in last [timespan] do not exceed the set threshold.
     *
     * Global threshold is calculated with a ratio from unsuccessful logins to total logins.
     * In order for bots not to increase the total login requests and thus manipulating the global threshold,
     * the same limit of failed login attempts per user is used also in place for successful logins.
     * If the user has 4 unsuccessful login attempts before throttling, he has also 4 successful login requests in
     * given timespan before experiencing the same throttling.
     *
     * @param RequestStatsData $ipStats login request summary from actual ip address
     * @param RequestStatsData $userStats login request summary by concerning email / coming for same user
     * @param string $email to get the latest request
     */
    private function performLoginCheck(RequestStatsData $ipStats, RequestStatsData $userStats, string $email): void
    {
        $throttleSuccess = $this->securitySettings['throttle_login_success'];
        // Reverse order to compare fails the longest delay first and then go down from there
        krsort($this->securitySettings['login_throttle_rule']);
        // Fails on specific user or coming from specific IP
        foreach ($this->securitySettings['login_throttle_rule'] as $requestLimit => $delay) {
            // Check that there aren't more login successes or failures than tolerated
            if (
                ($ipStats->loginFailures >= $requestLimit && $ipStats->loginFailures !== 0) ||
                ($throttleSuccess && $ipStats->loginSuccesses >= $requestLimit && $ipStats->loginSuccesses !== 0) ||
                ($userStats->loginFailures >= $requestLimit && $userStats->loginFailures !== 0) ||
                ($throttleSuccess && $userStats->loginSuccesses >= $requestLimit && $userStats->loginSuccesses !== 0)
            ) {
                // If truthy means: too many ip fails OR too many ip successes
                // OR too many failed login tries on specific user OR too many succeeding login requests on specific user

                // Retrieve the latest email sent for specific email or coming from ip
                $latestLoginRequest = $this->loginRequestFinder->findLatestLoginRequestFromEmailOrIp($email);
                // created_at in seconds
                $latestRequestTimestamp = (int)$latestLoginRequest->createdAt->format('U');
                $actualTime = new \DateTime();
                // Had issues when deploying the application and testing on github actions. date_default_timezone_set
                // isn't taken into account according to https://stackoverflow.com/a/44193886/9013718 because
                // time() and date() are timezone independent.
                $actualTimestamp = (int)$actualTime->setTimezone(new \DateTimeZone('Europe/Zurich'))
                    ->format('U');

                // Uncomment to debug
                // echo "\n" . 'Actual time: ' . $actualTime->format('H:i:s') . "\n" .
                //     'Latest login time (id: ' . $latestLoginRequest->id . '): ' .
                //     $latestLoginRequest->createdAt->format('H:i:s') . "\n" .
                //     'Delay: ' . $delay . "\n" . (is_numeric($delay) ? 'Time for next login: ' .
                //         (new \DateTime())->setTimestamp($delay + $latestRequestTimestamp)
                //             ->format('H:i:s') . "\n" . 'Security exception: ' .
                //         $securityException = $actualTimestamp < ($timeForNextLogin = $delay + $latestRequestTimestamp) : '') .
                //     "\n---- \n";

                $errMsg = 'Exceeded maximum of tolerated login requests.'; // Change in SecurityServiceTest as well
                if (is_numeric($delay)) {
                    // Check that time is in the future by comparing actual time with forced delay + to latest request
                    if ($actualTimestamp < ($timeForNextLogin = $delay + $latestRequestTimestamp)) {
                        $remainingDelay = $timeForNextLogin - $actualTimestamp;
                        throw new SecurityException($remainingDelay, SecurityType::USER_LOGIN, $errMsg);
                    }
                } elseif ($delay === 'captcha') {
                    $errMsg .= ' because of captcha';
                    // If delay not int, it means that 'captcha' is the delay
                    throw new SecurityException($delay, SecurityType::USER_LOGIN, $errMsg);
                }
            }
        }
        // Revert krsort() done earlier to prevent unexpected behaviour later when working with ['login_throttle_rule']
        ksort($this->securitySettings['login_throttle_rule']);
    }

    /**
     * Perform global login check - allow up to x percent of login failures.
     *
     * For the global request check set the login threshold to some ratio from unsuccessful to the total logins.
     * (permitting like 20% of total login requests to be unsuccessful).
     *
     * In order for bots not to increase the total login requests and thus manipulating the global threshold,
     * the same limit on failed login attempts per user is used also for successful logins.
     * If the user has 4 unsuccessful login attempts before throttling, he has also 4 successful login attempts
     * before experiencing the same throttling.
     */
    private function performGlobalLoginCheck(): void
    {
        // Cast all array values from string (what cake query builder returns) to int
        $loginAmountStats = array_map('intval', $this->loginRequestFinderRepository->getGlobalLoginAmountStats());

        // Calc allowed failure amount which is the given login_failure_percentage of the total login
        $failureThreshold = floor(
            $loginAmountStats['login_total'] / 100 * $this->securitySettings['login_failure_percentage']
        );
        // Actual failure amount have to be LESS than allowed failures amount (tested this way)
        // If there are not enough requests to be representative, the failureThreshold is increased to 20 meaning
        // at least 20 failed login attempts are allowed no matter the percentage
        // If percentage is 10, throttle begins at 200 login requests
        if (!($loginAmountStats['login_failures'] < $failureThreshold) && $failureThreshold > 20) {
            // If changed, update SecurityServiceTest distributed brute force test expected error message
            $msg = 'Maximum amount of tolerated unrestricted login requests reached site-wide.';
            throw new SecurityException('captcha', SecurityType::GLOBAL_LOGIN, $msg);
        }
    }
}
