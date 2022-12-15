<?php

namespace App\Domain\Authentication\Service;

use App\Domain\Settings;
use App\Domain\User\Data\UserData;
use App\Domain\Utility\Mailer;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * This class manages the emails sent in relation to registration.
 *
 * This is a centralised Service class containing email content and sending action.
 * Advantages:
 *  - Prevents other service classes of having the email responsibility (Single-responsibility principle)
 *  - Contents are centralised and can be changed easier without having to search them all over the code
 */
class RegistrationMailer
{
    private Email $email;

    /**
     * RegistrationMailer constructor.
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
     * Send verification token.
     *
     * @param UserData $user
     * @param array $queryParams
     *
     * @throws TransportExceptionInterface
     */
    public function sendRegisterVerificationToken(UserData $user, array $queryParams): void
    {
        // Send verification mail
        $this->email->subject('Account created')
        ->html(
            $this->mailer->getContentFromTemplate(
                'authentication/email/new-account.email.php',
                ['user' => $user, 'queryParams' => $queryParams]
            )
        )->to(new Address($user->email, $user->getFullName()));
        // Send email
        $this->mailer->send($this->email);
    }
}
