<?php

namespace App\Module\Authentication\Login\Domain\Service;

use App\Core\Infrastructure\Locale\LocaleConfigurator;
use App\Core\Infrastructure\Mail\Service\Mailer;
use App\Core\Infrastructure\Settings\Settings;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * This class manages the emails sent in relation to login.
 */
class LoginMailSender
{
    private Email $email;

    public function __construct(
        private readonly Mailer $mailer,
        private readonly LocaleConfigurator $localeConfigurator,
        Settings $settings,
    ) {
        $settings = $settings->get('public')['email'];
        // Create email object
        $this->email = new Email();
        // Send auth emails from domain
        $this->email->from(new Address($settings['main_sender_address'], $settings['main_sender_name']))->replyTo(
            $settings['main_contact_email']
        )->priority(Email::PRIORITY_HIGH);
    }

    /**
     * When a user tries to log in but their status is unverified.
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
                    'authentication/email/' . $this->localeConfigurator->getLanguageCodeForPath() .
                    'login-but-unverified.email.php',
                    ['userFullName' => $fullName, 'queryParams' => $queryParamsWithToken]
                )
            )->to(new Address($email, $fullName));
        // Send email
        $this->mailer->send($this->email);
    }

    /**
     * When a user tries to log in but their status is suspended.
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
                    'authentication/email/' . $this->localeConfigurator->getLanguageCodeForPath() .
                    'login-but-suspended.email.php',
                    ['userFullName' => $fullName]
                )
            )->to(new Address($email, $fullName));
        // Send email
        $this->mailer->send($this->email);
    }

    /**
     * When a user tries to log in but their status is suspended.
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
                    'authentication/email/' . $this->localeConfigurator->getLanguageCodeForPath() .
                    'login-but-locked.email.php',
                    ['userFullName' => $fullName, 'queryParams' => $queryParamsWithToken]
                )
            )->to(new Address($email, $fullName));
        // Send email
        $this->mailer->send($this->email);
    }
}
