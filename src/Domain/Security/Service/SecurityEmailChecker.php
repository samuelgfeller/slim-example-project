<?php

namespace App\Domain\Security\Service;

use App\Domain\Security\Enum\SecurityType;
use App\Domain\Security\Exception\SecurityException;
use App\Domain\Security\Repository\EmailLogFinderRepository;
use App\Infrastructure\Utility\Settings;

class SecurityEmailChecker
{
    private array $securitySettings;

    public function __construct(
        private readonly SecurityCaptchaVerifier $captchaVerifier,
        private readonly EmailRequestFinder $emailRequestFinder,
        private readonly EmailLogFinderRepository $requestFinderRepository,
        Settings $settings
    ) {
        $this->securitySettings = $settings->get('security');
    }

    /**
     * Threat: Email abuse (sending a lot of emails may be costly).
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
     * @param string|null $email
     * @param string|null $reCaptchaResponse
     */
    public function performEmailAbuseCheck(?string $email, ?string $reCaptchaResponse = null): void
    {
        if ($this->securitySettings['throttle_user_email'] === true && isset($email)) {
            $validCaptcha = false;
            // reCAPTCHA verification
            if ($reCaptchaResponse !== null) {
                $validCaptcha = $this->captchaVerifier->verifyReCaptcha(
                    $reCaptchaResponse,
                    SecurityType::USER_EMAIL
                );
            }
            // If captcha is valid the other security checks don't have to be made
            if ($validCaptcha !== true) {
                $emailsAmount = $this->emailRequestFinder->findEmailAmountInSetTimespan($email);
                // Email checks (register, password recovery, other with email)
                $this->performEmailRequestsCheck($emailsAmount, $email);
                // Global email check
                $this->performGlobalEmailCheck();
            }
        }
    }

    /**
     * Make email abuse check for requests coming from same ip
     * or concerning the same email address.
     *
     * @param int $emailsAmount amount of emails sent in the last timespan
     * @param string $email
     */
    private function performEmailRequestsCheck(
        int $emailsAmount,
        string $email
    ): void {
        // Reverse order to compare fails the longest delay first and then go down from there
        krsort($this->securitySettings['user_email_throttle_rule']);
        // Fails on specific user or coming from specific IP
        foreach ($this->securitySettings['user_email_throttle_rule'] as $requestLimit => $delay) {
            // If sent emails in the last given timespan is greater than the tolerated amount of requests with email per timespan
            if ($emailsAmount >= $requestLimit) {
                // Retrieve the latest email sent created_at in seconds
                $latestEmailTimestamp = $this->emailRequestFinder->findLastEmailRequestTimestamp($email);

                $errMsg = 'Exceeded maximum of tolerated emails.'; // Change in SecurityServiceTest as well
                if (is_numeric($delay)) {
                    // Check that time is in the future by comparing actual time with forced delay + to the latest request
                    if (time() < ($timeForNextRequest = $delay + $latestEmailTimestamp)) {
                        $remainingDelay = (int)($timeForNextRequest - time());
                        throw new SecurityException($remainingDelay, SecurityType::USER_EMAIL, $errMsg);
                    }
                } elseif ($delay === 'captcha') {
                    throw new SecurityException($delay, SecurityType::USER_EMAIL, $errMsg);
                }
            }
        }
        // Revert krsort() done earlier to prevent unexpected behaviour later when working with ['login_throttle_rule']
        ksort($this->securitySettings['login_throttle_rule']);
    }

    /**
     * Protection against email abuse.
     */
    private function performGlobalEmailCheck(): void
    {
        // Order of calls on getGlobalSentEmailAmount() matters in test. First daily and then monthly should be called

        // Check emails for daily threshold
        if (isset($this->securitySettings['global_daily_email_threshold'])) {
            $sentEmailAmountInLastDay = $this->requestFinderRepository->getGlobalSentEmailAmount(1);
            // If sent emails exceed or equal the given threshold
            if ($sentEmailAmountInLastDay >= $this->securitySettings['global_daily_email_threshold']) {
                $msg = 'Maximum amount of unrestricted email sending daily reached site-wide.';
                throw new SecurityException('captcha', SecurityType::GLOBAL_EMAIL, $msg);
            }
        }

        // Check emails for monthly threshold
        if (isset($this->securitySettings['global_monthly_email_threshold'])) {
            $sentEmailAmountInLastMonth = $this->requestFinderRepository->getGlobalSentEmailAmount(30);
            // If sent emails exceed or equal the given threshold
            if ($sentEmailAmountInLastMonth >= $this->securitySettings['global_monthly_email_threshold']) {
                $msg = 'Maximum amount of unrestricted email sending monthly reached site-wide.';
                throw new SecurityException('captcha', SecurityType::GLOBAL_EMAIL, $msg);
            }
        }
    }
}
