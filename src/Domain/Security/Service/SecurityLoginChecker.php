<?php

namespace App\Domain\Security\Service;

use App\Domain\Security\Enum\SecurityType;
use App\Domain\Security\Exception\SecurityException;
use App\Domain\Security\Repository\LoginLogFinderRepository;
use App\Domain\Utility\Settings;
use App\Test\Unit\Security\SecurityLoginCheckerTest;

class SecurityLoginChecker
{
    private array $securitySettings;

    public function __construct(
        private readonly SecurityCaptchaVerifier $captchaVerifier,
        private readonly LoginRequestFinder $loginRequestFinder,
        private readonly LoginLogFinderRepository $loginRequestFinderRepository,
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
    public function performLoginSecurityCheck(string $email, ?string $reCaptchaResponse = null): void
    {
        if ($this->securitySettings['throttle_login'] === true) {
            // Standard verification has to be done before captcha check as the captcha may be needed for email
            // verification when email is sent to a non-active user for instance
            try {
                $summary = $this->loginRequestFinder->findLoginLogEntriesInTimeLimit($email);
                // Most strict. Very low limit on failed requests for specific user or coming from an ip
                $this->performLoginCheck($summary['logins_by_ip'], $summary['logins_by_email'], $email);
                // Global login check
                $this->performGlobalLoginCheck();
            } catch (SecurityException $securityException) {
                // reCAPTCHA check done AFTER standard login checks as captcha can be verified only once, and it
                // may be required later for the email verification (to send email to a non-active user)
                if ($reCaptchaResponse !== null) {
                    $this->captchaVerifier->verifyReCaptcha(
                        $reCaptchaResponse,
                        SecurityType::USER_LOGIN
                    );
                } else {
                    // If security exception was thrown and reCaptcha response is null, throw exception
                    throw $securityException;
                }
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
     * @param array{successes: int, failures: int} $loginsByIp login request from ip address
     * @param array{successes: int, failures: int} $loginsByEmail login request coming from same email
     * @param string $email to get the latest request
     */
    private function performLoginCheck(array $loginsByIp, array $loginsByEmail, string $email): void
    {
        // Reverse order to compare fails the longest delay first and then go down from there
        krsort($this->securitySettings['login_throttle_rule']);
        // Fails on specific user or coming from specific IP
        foreach ($this->securitySettings['login_throttle_rule'] as $requestLimit => $delay) {
            // Check that there aren't more login successes or failures than tolerated
            if (
                ($loginsByIp['failures'] >= $requestLimit && $loginsByIp['failures'] !== 0)
                || ($loginsByEmail['failures'] >= $requestLimit && $loginsByEmail['failures'] !== 0)
            ) {
                // If truthy means: too many ip fails OR too many ip successes
                // OR too many failed login tries on specific user OR too many succeeding login requests on specific user

                // Retrieve the latest email sent for specific email or coming from ip
                $latestLoginTimestamp = $this->loginRequestFinder->findLatestLoginRequestTimestamp($email);
                // created_at in seconds
                $currentTime = new \DateTime();
                // Had issues when deploying the application and testing on github actions. date_default_timezone_set
                // isn't taken into account according to https://stackoverflow.com/a/44193886/9013718 because
                // time() and date() are timezone independent.
                $currentTimestamp = (int)$currentTime->setTimezone(new \DateTimeZone('Europe/Zurich'))
                    ->format('U');

                // Uncomment to debug
                // echo "\n" . 'Current time: ' . $currentTime->format('H:i:s') . "\n" .
                //     'Latest login time: ' .
                //     (new \DateTime($latestLoginTimestamp))->format('H:i:s') . "\n" .
                //     'Delay: ' . $delay . "\n" . (is_numeric($delay) ? 'Time for next login: ' .
                //         (new \DateTime())->setTimestamp($delay + $latestLoginTimestamp)
                //             ->format('H:i:s') . "\n" . 'Security exception: ' .
                //         $securityException = $currentTimestamp < ($timeForNextLogin = $delay + $latestLoginTimestamp) : '') .
                //     "\n---- \n";
                /** Asserted in @see SecurityLoginCheckerTest (public message is defferent) */
                $errMsg = 'Exceeded maximum of tolerated login requests';
                if (is_numeric($delay)) {
                    // Check that time is in the future by comparing actual time with forced delay + to latest request
                    if ($currentTimestamp < ($timeForNextLogin = $delay + $latestLoginTimestamp)) {
                        $remainingDelay = (int)($timeForNextLogin - $currentTimestamp);
                        throw new SecurityException($remainingDelay, SecurityType::USER_LOGIN, $errMsg);
                    }
                } elseif ($delay === 'captcha') {
                    $errMsg .= ' with captcha';
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
        // Making sure that values returned from repository are cast into integers
        $loginAmountSummary = array_map('intval', $this->loginRequestFinderRepository->getGlobalLoginAmountSummary());

        // Calc allowed failure amount which is the given login_failure_percentage of the total login
        $failureThreshold = floor(
            $loginAmountSummary['total_amount'] / 100 * $this->securitySettings['login_failure_percentage']
        );
        // Actual failure amount have to be LESS than allowed failures amount (tested this way)
        // If there are not enough requests to be representative, the failureThreshold is increased to 20 meaning
        // at least 20 failed login attempts are allowed no matter the percentage
        // If percentage is 10, throttle begins at 200 login requests
        if (!($loginAmountSummary['failures'] < $failureThreshold) && $failureThreshold > 20) {
            // If changed, update SecurityServiceTest distributed brute force test expected error message
            $msg = 'Maximum amount of tolerated unrestricted login requests reached site-wide.';
            throw new SecurityException('captcha', SecurityType::GLOBAL_LOGIN, $msg);
        }
    }
}
