<?php

namespace App\Domain\Authentication\Service;

use App\Domain\Settings;
use App\Domain\User\Data\UserData;
use App\Domain\Utility\Mailer;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * This class manages the emails sent in relation to login.
 */
class LoginMailer
{
    private Email $email;

    /**
     * LoginMailer constructor.
     *
     * @param Mailer $mailer email sender and helper
     * @param Settings $settings
     */
    public function __construct(
        private readonly Mailer $mailer,
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
     * @param UserData $user
     * @param array $queryParamsWithToken
     *
     * @throws TransportExceptionInterface
     */
    public function sendInfoToUnverifiedUser(UserData $user, array $queryParamsWithToken): void
    {
        // Send verification mail
        $this->email->subject('You tried to login but are unverified')
            ->html(
                $this->mailer->getContentFromTemplate(
                    'authentication/email/login-but-unverified.email.php',
                    ['user' => $user, 'queryParams' => $queryParamsWithToken]
                )
            )->to(new Address($user->email, $user->getFullName()));
        // Send email
        $this->mailer->send($this->email);
    }

    /**
     * When user tries to log in but his status is suspended.
     *
     * @param UserData $user
     *
     * @throws TransportExceptionInterface
     */
    public function sendInfoToSuspendedUser(UserData $user): void
    {
        // Send verification mail
        $this->email->subject('You tried to login but are suspended')
            ->html(
                $this->mailer->getContentFromTemplate(
                    'authentication/email/login-but-suspended.email.php',
                    ['user' => $user]
                )
            )->to(new Address($user->email, $user->getFullName()));
        // Send email
        $this->mailer->send($this->email);
    }

    /**
     * When user tries to log in but his status is suspended.
     *
     * @param UserData $user
     * @param array $queryParamsWithToken
     *
     * @throws TransportExceptionInterface
     */
    public function sendInfoToLockedUser(UserData $user, array $queryParamsWithToken): void
    {
        // Send verification mail
        $this->email->subject('You tried to login but are locked')
            ->html(
                $this->mailer->getContentFromTemplate(
                    'authentication/email/login-but-locked.email.php',
                    ['user' => $user, 'queryParams' => $queryParamsWithToken]
                )
            )->to(new Address($user->email, $user->getFullName()));
        // Send email
        $this->mailer->send($this->email);
    }
}
