<?php


namespace App\Domain\Security;


use App\Domain\Settings;
use App\Infrastructure\Security\RequestTrackRepository;
use App\Infrastructure\User\UserRepository;

class SecurityService
{
    private array $settings;

    private array $globalRequests;
    // Coming from ip
    private array $ipRequests;
    // Concerning specific email
    private array $userRequests;

    public function __construct(
        private UserRepository $userRepository,
        private RequestTrackRepository $requestTrackRepository,
        Settings $settings
    ) {
        $this->settings = $settings->get('security');
    }

    /**
     * Sensitive requests are stored in table request_track.
     *  - register (failed or not)
     *  - login (failed or not)
     *  - password recovery submit
     *  - all requests that send an email
     *
     * @param string $email User
     *
     * @throws SecurityException
     */
    public function performSecurityCheck(string $email): void
    {
        // All stats in last timespan
        $this->globalRequests = $this->requestTrackRepository->getGlobalRequestStats($this->settings['timespan']);

        // Stats coming from ip in last timespan
        $this->ipRequests = $this->requestTrackRepository->getIpRequestStats(
            $_SERVER['REMOTE_ADDR'],
            $this->settings['timespan']
        );

        // Stats concerning given email in last timespan
        $this->userRequests = $this->requestTrackRepository->getUserRequestStats($email, $this->settings['timespan']);

        // Fail check (failed login attempt)
        // Most strict. Very low limit on failed requests for specific emails or coming from a specific ip
        $this->performLoginRequestCheck($email);
        // Global fail check
        $this->performGlobalLoginCheck();

        // Email checks (register, password recovery, other with email)
        $this->performEmailRequestsCheck($email);
        // Global email check
        $this->performGlobalEmailCheck();
        // Global sensitive request check not implemented yet as there are none that are nor fail or email
    }

    /**
     * Check that login requests in last [timespan] do not exceed the set threshold.
     *
     * Global threshold is calculated with a ratio from unsuccessful logins to total logins.
     * In order for bots not to increase the total login requests and thus manipulating the global threshold,
     * the same limit on failed login attempts per user is used also for successful logins.
     * If the user has 4 unsuccessful login attempts before throttling, he has also 4 successful login requests in
     * given timespan before experiencing the same throttling.
     *
     * @param string $email
     */
    private function performLoginRequestCheck(string $email): void
    {
        // Reverse order to compare fails longest delay first and then go down from there
        krsort($this->settings['login_throttle']);
        // Fails on specific user or coming from specific IP
        foreach ($this->settings['login_throttle'] as $requestLimit => $delay) {
            // Check that there aren't more login successes or failures than tolerated
            if (
                (int)$this->ipRequests['login_failures'] >= $requestLimit ||
                (int)$this->ipRequests['login_successes'] >= $requestLimit ||
                (int)$this->userRequests['login_failures'] >= $requestLimit ||
                (int)$this->userRequests['login_successes'] >= $requestLimit
            ) {
                // If truthy means: too many ip fails OR too many ip successes
                // OR too many failed login tries on specific user OR too many succeeding login requests on specific user

                // Retrieve latest email sent for specific email or coming from ip
                $latestLoginRequest = $this->requestTrackRepository->findLatestLoginRequestFromUserOrIp(
                    $email,
                    $_SERVER['REMOTE_ADDR']
                );

                $msg = 'Exceeded maximum of tolerated login requests.'; // Change in SecurityServiceTest as well
                if (is_numeric($delay)) {
                    // created_at in seconds
                    $latest = (int)date('U', strtotime($latestLoginRequest['created_at']));
                    $remainingDelay = $latest - time() + $delay;

                    throw new SecurityException($remainingDelay, SecurityException::USER_LOGIN, $msg);
                }

                // If delay not int, it means that 'captcha' is the delay
                throw new SecurityException($delay, SecurityException::USER_LOGIN, $msg);
            }
        }
        // Revert krsort() done earlier to prevent unexpected behaviour later when working with ['login_throttle']
        ksort($this->settings['login_throttle']);
    }

    /**
     * @param string $email
     *
     * @throws SecurityException
     */
    private function performEmailRequestsCheck(string $email): void
    {
        // Reverse order to compare fails longest delay first and then go down from there
        krsort($this->settings['user_email_throttle']);
        // Fails on specific user or coming from specific IP
        foreach ($this->settings['user_email_throttle'] as $requestLimit => $delay) {
            // If sent emails in the last given timespan is greater than the tolerated amount of requests with email per timespan
            if (
                (int)$this->ipRequests['sent_emails'] >= $requestLimit ||
                (int)$this->userRequests['sent_emails'] >= $requestLimit
            ) {
                // Retrieve latest email sent for specific email or coming from ip
                $latestEmailRequestFromUser = $this->requestTrackRepository->findLatestEmailRequestFromUserOrIp(
                    $email,
                    $_SERVER['REMOTE_ADDR']
                );

                $msg = 'Exceeded maximum of tolerated emails.'; // Change in SecurityServiceTest as well
                if (is_numeric($delay)) {
                    // created_at in seconds
                    $latest = (int)date('U', strtotime($latestEmailRequestFromUser['created_at']));
                    $remainingDelay = $latest - time() + $delay;

                    throw new SecurityException($remainingDelay, SecurityException::USER_EMAIL, $msg);
                }

                // If delay not int, it means that 'captcha' is the delay
                throw new SecurityException($delay, SecurityException::USER_EMAIL, $msg);
            }
        }
        // Revert krsort() done earlier to prevent unexpected behaviour later when working with ['login_throttle']
        ksort($this->settings['login_throttle']);
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
        $loginAmountStats = $this->requestTrackRepository->getLoginAmountStats();
        // Calc integer allowed failure amount from given percentage and total login
        $failureThreshold = $loginAmountStats['login_total'] / 100 * $this->settings['login_failure_percentage'];
        // Actual failure amount have to be LESS than allowed failures amount (tested this way)
        if (!($loginAmountStats['login_failures'] < $failureThreshold) && $loginAmountStats['login_failures'] !== 0) {
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
        if (!empty($this->settings['global_daily_email_threshold'])) {
            $sentEmailAmountInLastDay = $this->requestTrackRepository->getGlobalSentEmailAmount(1);
            // If sent emails exceed or equal the given threshold
            if ($sentEmailAmountInLastDay >= $this->settings['global_daily_email_threshold']) {
                $msg = 'Maximum amount of unrestricted email sending daily reached site-wide.';
                throw new SecurityException('captcha', SecurityException::GLOBAL_EMAIL, $msg);
            }
        }

        // Check emails for monthly threshold
        if (!empty($this->settings['global_monthly_email_threshold'])) {
            $sentEmailAmountInLastMonth = $this->requestTrackRepository->getGlobalSentEmailAmount(30);
            // If sent emails exceed or equal the given threshold
            if ($sentEmailAmountInLastMonth >= $this->settings['global_monthly_email_threshold']) {
                $msg = 'Maximum amount of unrestricted email sending monthly reached site-wide.';
                throw new SecurityException('captcha', SecurityException::GLOBAL_EMAIL, $msg);
            }
        }
    }
}