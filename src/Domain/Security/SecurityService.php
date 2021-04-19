<?php


namespace App\Domain\Security;


use App\Domain\Settings;
use App\Infrastructure\Security\RequestTrackRepository;
use App\Infrastructure\User\UserRepository;

/**
 * Sensitive requests are stored in table request_track.
 */
class SecurityService
{
    private array $securitySettings;
    private array $googleSettings;

    // Coming from ip
    private array $ipRequests;
    // Concerning specific user/email
    private array $userRequests;
    private string $email;

    public function __construct(
        private UserRepository $userRepository,
        private RequestTrackRepository $requestTrackRepository,
        Settings $settings
    ) {
        $this->securitySettings = $settings->get('security');
        $this->googleSettings = $settings->get('google');
    }

    /**
     * Register new login request
     *
     * @param string $email
     * @param string $ip
     * @param bool $success if login was successful or a failure
     */
    public function newLoginRequest(string $email, string $ip, bool $success): void
    {
        $this->requestTrackRepository->insertLoginRequest($email, $ip, $success);
    }

    /**
     * Register new request where an email has been sent
     *
     * @param string $email
     * @param string $ip
     */
    public function newEmailRequest(string $email, string $ip): void
    {
        $this->requestTrackRepository->insertEmailRequest($email, $ip);
    }

    /**
     * Retrieve and populate attributes with stats from database
     */
    private function retrieveAndSetStats(): void
    {
        // Stats coming from ip in last timespan (and cast to int)
        $this->ipRequests = array_map(
            'intval',
            $this->requestTrackRepository->getIpRequestStats(
                $_SERVER['REMOTE_ADDR'],
                $this->securitySettings['timespan']
            )
        );

        // Stats concerning given email in last timespan
        $this->userRequests = array_map(
            'intval',
            $this->requestTrackRepository->getUserRequestStats($this->email, $this->securitySettings['timespan'])
        );
    }

    /**
     * Threats:
     * - Rapid fire attacks (when bots try to log in with 1000 different passwords on one user account)
     * - Distributed brute force attacks (try to log in 1000 different users with most common password)
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
        // reCAPTCHA verification
        $validCaptcha = false;
        if ($reCaptchaResponse !== null) {
            $validCaptcha = $this->verifyReCaptcha($reCaptchaResponse, SecurityException::USER_LOGIN);
        }
        // If captcha is valid the other security checks don't have to be made
        if ($validCaptcha !== true) {
            // Set attributes
            $this->email = $email;
            $this->retrieveAndSetStats();

            // Most strict. Very low limit on failed requests for specific emails or coming from a specific ip
            $this->performLoginCheck();
            // Global login check
            $this->performGlobalLoginCheck();
        }
    }

    /**
     * Threat: Email abuse (sending a lot of emails may be costly)
     *
     * Throttle behaviour: Limit email sending
     * - After x amount of emails sent from ip or user they have 3 thresholds with
     *    different waiting times
     * - After the last threshold is reached, captcha is required for every email sent
     * - Limit applies to last [timespan]. If waited enough, users can send unrestricted emails again
     * - Globally there are two optional rules:
     *   1. Defined daily limit - after it is reached, captcha is required for every user
     *   2. Monthly limit - after it is reached, captcha is required for every user (mailgun resets after 1st)
     *
     * Perform email abuse check
     * - coming from the same ip address
     * - concerning a specific email address
     * - global email requests
     *
     * @param string $email
     * @param string|null $reCaptchaResponse
     */
    public function performEmailAbuseCheck(string $email, string|null $reCaptchaResponse = null): void
    {
        $validCaptcha = false;
        // reCAPTCHA verification
        if ($reCaptchaResponse !== null) {
            $validCaptcha = $this->verifyReCaptcha($reCaptchaResponse, SecurityException::USER_EMAIL);
        }
        // If captcha is valid the other security checks don't have to be made
        if ($validCaptcha !== true) {
            // Set attributes
            $this->email = $email;
            $this->retrieveAndSetStats();

            // Email checks (register, password recovery, other with email)
            $this->performEmailRequestsCheck();
            // Global email check
            $this->performGlobalEmailCheck();
        }
    }


    // -- Private methods --

    /**
     * Ask google API if reCAPTCHA user response is correct or not
     *
     * @param string $reCaptchaResponse
     * @param string $type
     * @return bool true when correct otherwise SecurityException
     * @throws SecurityException
     */
    private function verifyReCaptcha(string $reCaptchaResponse, string $type): bool
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' .
            urlencode($this->googleSettings['recaptcha']) . '&response=' . urlencode($reCaptchaResponse);
        $verificationResponse = file_get_contents($url);
        if (
            $verificationResponse !== false &&
            json_decode($verificationResponse, true, 512, JSON_THROW_ON_ERROR)['success']
        ) {
            return true;
        }
        $errMsg = 'reCAPTCHA verification failed';
        throw new SecurityException('captcha', $type, $errMsg);
    }


    /**
     * Check that login requests in last [timespan] do not exceed the set threshold.
     *
     * Global threshold is calculated with a ratio from unsuccessful logins to total logins.
     * In order for bots not to increase the total login requests and thus manipulating the global threshold,
     * the same limit on failed login attempts per user is used also for successful logins.
     * If the user has 4 unsuccessful login attempts before throttling, he has also 4 successful login requests in
     * given timespan before experiencing the same throttling.
     */
    private function performLoginCheck(): void
    {
        // Reverse order to compare fails longest delay first and then go down from there
        krsort($this->securitySettings['login_throttle']);
        // Fails on specific user or coming from specific IP
        foreach ($this->securitySettings['login_throttle'] as $requestLimit => $delay) {
            // Check that there aren't more login successes or failures than tolerated
            if (
                ($this->ipRequests['login_failures'] >= $requestLimit && $this->ipRequests['login_failures'] !== 0) ||
                ($this->ipRequests['login_successes'] >= $requestLimit && $this->ipRequests['login_successes'] !== 0) ||
                ($this->userRequests['login_failures'] >= $requestLimit &&
                    $this->userRequests['login_failures'] !== 0) ||
                ($this->userRequests['login_successes'] >= $requestLimit &&
                    $this->userRequests['login_successes'] !== 0)
            ) {
                // If truthy means: too many ip fails OR too many ip successes
                // OR too many failed login tries on specific user OR too many succeeding login requests on specific user

                // Retrieve latest email sent for specific email or coming from ip
                $latestLoginRequest = $this->requestTrackRepository->findLatestLoginRequestFromUserOrIp(
                    $this->email,
                    $_SERVER['REMOTE_ADDR']
                );

                $errMsg = 'Exceeded maximum of tolerated login requests.'; // Change in SecurityServiceTest as well
                if (is_numeric($delay)) {
                    // created_at in seconds
                    $latest = (int)date('U', strtotime($latestLoginRequest['created_at']));

                    // Check that time is in the future by comparing actual time with forced delay + to latest request
                    if (($time = time()) < ($timeForNextLogin = $delay + $latest)) {
                        $remainingDelay = $timeForNextLogin - $time;
                        throw new SecurityException($remainingDelay, SecurityException::USER_LOGIN, $errMsg);
                    }
                } elseif ($delay === 'captcha') {
                    // If delay not int, it means that 'captcha' is the delay
                    throw new SecurityException($delay, SecurityException::USER_LOGIN, $errMsg);
                }
            }
        }
        // Revert krsort() done earlier to prevent unexpected behaviour later when working with ['login_throttle']
        ksort($this->securitySettings['login_throttle']);
    }

    /**
     * Make email abuse check for requests coming from same ip
     * or concerning the same email address
     *
     * @throws SecurityException
     */
    private function performEmailRequestsCheck(): void
    {
        // Reverse order to compare fails longest delay first and then go down from there
        krsort($this->securitySettings['user_email_throttle']);
        // Fails on specific user or coming from specific IP
        foreach ($this->securitySettings['user_email_throttle'] as $requestLimit => $delay) {
            // If sent emails in the last given timespan is greater than the tolerated amount of requests with email per timespan
            if (
                $this->ipRequests['sent_emails'] >= $requestLimit || $this->userRequests['sent_emails'] >= $requestLimit
            ) {
                // Retrieve latest email sent for specific email or coming from ip
                $latestEmailRequestFromUser = $this->requestTrackRepository->findLatestEmailRequestFromUserOrIp(
                    $this->email,
                    $_SERVER['REMOTE_ADDR']
                );

                $errMsg = 'Exceeded maximum of tolerated emails.'; // Change in SecurityServiceTest as well
                if (is_numeric($delay)) {
                    // created_at in seconds
                    $latest = (int)date('U', strtotime($latestEmailRequestFromUser['created_at']));

                    // Check that time is in the future by comparing actual time with forced delay + to latest request
                    if (time() < ($timeForNextRequest = $delay + $latest)) {
                        $remainingDelay = $timeForNextRequest - time();
                        throw new SecurityException($remainingDelay, SecurityException::USER_EMAIL, $errMsg);
                    }
                } elseif ($delay === 'captcha') {
                    throw new SecurityException($delay, SecurityException::USER_EMAIL, $errMsg);
                }
            }
        }
        // Revert krsort() done earlier to prevent unexpected behaviour later when working with ['login_throttle']
        ksort($this->securitySettings['login_throttle']);
    }

    /**
     * Perform global login check - allow up to x percent of login failures
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
        $loginAmountStats = array_map('intval', $this->requestTrackRepository->getGlobalLoginAmountStats());

        // Calc integer allowed failure amount from given percentage and total login
        $failureThreshold = floor(
            $loginAmountStats['login_total'] / 100 * $this->securitySettings['login_failure_percentage']
        );
        // Actual failure amount have to be LESS than allowed    failures amount (tested this way)
        if (!($loginAmountStats['login_failures'] < $failureThreshold) && $failureThreshold > 20) {
            // If changed, update SecurityServiceTest distributed brute force test expected error message
            $msg = 'Maximum amount of tolerated unrestricted login requests reached site-wide.';
            throw new SecurityException('captcha', SecurityException::GLOBAL_LOGIN, $msg);
        }
    }

    /**
     * Protection against email abuse
     */
    private function performGlobalEmailCheck(): void
    {
        // Order of calls on getGlobalSentEmailAmount() matters in test. First daily and then monthly should be called

        // Check emails for daily threshold
        if (!empty($this->securitySettings['global_daily_email_threshold'])) {
            $sentEmailAmountInLastDay = (int)$this->requestTrackRepository->getGlobalSentEmailAmount(1);
            // If sent emails exceed or equal the given threshold
            if ($sentEmailAmountInLastDay >= $this->securitySettings['global_daily_email_threshold']) {
                $msg = 'Maximum amount of unrestricted email sending daily reached site-wide.';
                throw new SecurityException('captcha', SecurityException::GLOBAL_EMAIL, $msg);
            }
        }

        // Check emails for monthly threshold
        if (!empty($this->securitySettings['global_monthly_email_threshold'])) {
            $sentEmailAmountInLastMonth = (int)$this->requestTrackRepository->getGlobalSentEmailAmount(30);
            // If sent emails exceed or equal the given threshold
            if ($sentEmailAmountInLastMonth >= $this->securitySettings['global_monthly_email_threshold']) {
                $msg = 'Maximum amount of unrestricted email sending monthly reached site-wide.';
                throw new SecurityException('captcha', SecurityException::GLOBAL_EMAIL, $msg);
            }
        }
    }
}