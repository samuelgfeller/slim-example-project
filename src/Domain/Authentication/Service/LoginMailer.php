<?php

namespace App\Domain\Authentication\Service;

use App\Common\LocaleHelper;
use App\Domain\Service\Infrastructure\Mailer;
use App\Domain\Utility\Settings;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * This class manages the emails sent in relation to login.
 */
class LoginMailer
{
    private Email $email;

    public function __construct(
        private readonly Mailer $mailer,
        private readonly LocaleHelper $localeHelper,
        Settings $settings
    ) {
        $settings = $settings->get('public')['email'];
        // Create email object
        $this->email = new Email();
        // Send auth emails from domain
        $this->email->from(new Address($settings['main_sender_address'], $settings['main_sender_name']))->replyTo(
            $settings['main_contact_address']
        )->priority(Email::PRIORITY_HIGH);
    }

    /**
     * When user tries to log in but his status is unverified.
     *
     * @param string $email
     * @param string $fullName
     * @param array $queryParamsWithToken
     *
     * @throws TransportExceptionInterface
     */
    public function sendInfoToUnverifiedUser(string $email, string $fullName, array $queryParamsWithToken): void
    {
        // Send verification mail
        $this->email->subject(__('Login failed because your account is unverified'))
            ->html(
                $this->mailer->getContentFromTemplate(
                    'authentication/email/' . $this->localeHelper->getLanguageCodeForPath() .
                    'login-but-unverified.email.php',
                    ['userFullName' => $fullName, 'queryParams' => $queryParamsWithToken]
                )
            )->to(new Address($email, $fullName));
        // Send email
        $this->mailer->send($this->email);
    }

    /**
     * When user tries to log in but his status is suspended.
     *
     * @param string $email
     * @param string $fullName
     *
     * @throws TransportExceptionInterface
     */
    public function sendInfoToSuspendedUser(string $email, string $fullName): void
    {
        // Send verification mail
        $this->email->subject(__('Login failed because your account is suspended'))
            ->html(
                $this->mailer->getContentFromTemplate(
                    'authentication/email/' . $this->localeHelper->getLanguageCodeForPath() .
                    'login-but-suspended.email.php',
                    ['userFullName' => $fullName]
                )
            )->to(new Address($email, $fullName));
        // Send email
        $this->mailer->send($this->email);
    }

    /**
     * When user tries to log in but his status is suspended.
     *
     * @param string $email
     * @param string $fullName
     * @param array $queryParamsWithToken
     *
     * @throws TransportExceptionInterface
     */
    public function sendInfoToLockedUser(string $email, string $fullName, array $queryParamsWithToken): void
    {
        // Send verification mail
        $this->email->subject(__('Login failed because your account is locked'))
            ->html(
                $this->mailer->getContentFromTemplate(
                    'authentication/email/' . $this->localeHelper->getLanguageCodeForPath() .
                    'login-but-locked.email.php',
                    ['userFullName' => $fullName, 'queryParams' => $queryParamsWithToken]
                )
            )->to(new Address($email, $fullName));
        // Send email
        $this->mailer->send($this->email);
    }
}
