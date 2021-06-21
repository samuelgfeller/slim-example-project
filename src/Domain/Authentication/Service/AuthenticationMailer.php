<?php


namespace App\Domain\Authentication\Service;

use App\Domain\Settings;
use App\Domain\User\DTO\User;
use App\Domain\Utility\Mailer;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * This class manages the emails sent from the Authentication namespace.
 *
 * This is a centralised Service class containing email content and sending action.
 * Advantages:
 *  - Prevents other service classes of having the email responsibility (Single-responsibility principle)
 *  - Contents are centralized and can be changed easier without having to search them all over the code
 */
class AuthenticationMailer
{

    private Email $email;

    /**
     * AuthenticationMailer constructor.
     *
     * @param Mailer $mailer email sender and helper
     * @param Settings $settings
     */
    public function __construct(
        private Mailer $mailer,
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
     * Send verification token
     *
     * @param User $user
     * @param array $queryParams
     * @throws TransportExceptionInterface
     */
    public function sendRegisterVerificationToken(User $user, array $queryParams): void
    {
        // Send verification mail
        $this->email->subject('One more step to register')
        ->html(
            $this->mailer->getContentFromTemplate(
                'authentication/email/register.email.php',
                ['user' => $user, 'queryParams' => $queryParams]
            )
        )->to(new Address($user->email, $user->name));
        // Send email
        $this->mailer->send($this->email);
    }

    /**
     * Send email to the already existing user with status "suspended" when someone tries to register with the
     * same email address.
     *
     * @param User $existingUser
     * @throws TransportExceptionInterface
     */
    public function sendRegisterExistingActiveUser(User $existingUser): void
    {
        // Send info mail to email address holder
        // Subject asserted in testRegisterUser_alreadyExistingActiveUser
        $this->email->subject('Someone tried to create an account with your address')->html(
            $this->mailer->getContentFromTemplate(
                'authentication/email/register-on-existing-active.php',
                ['user' => $existingUser]
            )
        )->to(new Address($existingUser->email, $existingUser->name));
        // Send email
        $this->mailer->send($this->email);
    }

    /**
     * Send email to the already existing user with status "suspended" when someone tries to register with the
     * same email address.
     *
     * @param User $existingUser
     * @throws TransportExceptionInterface
     */
    public function sendRegisterExistingSuspendedUser(User $existingUser): void
    {
        // Send info mail to email address holder
        // Subject asserted in testRegisterUser_alreadyExistingActiveUser
        $this->email->subject('Someone tried to create an account with your address')->html(
            $this->mailer->getContentFromTemplate(
                'authentication/email/register-on-existing-suspended.php',
                ['user' => $existingUser]
            )
        )->to(new Address($existingUser->email, $existingUser->name));
        // Send email
        $this->mailer->send($this->email);
    }

    /**
     * Send email to the already existing user with status "locked" when someone tries to register with the
     * same email address.
     *
     * @param User $existingUser
     * @throws TransportExceptionInterface
     */
    public function sendRegisterExistingLockedUser(User $existingUser): void
    {
        // Send info mail to email address holder
        // Subject asserted in testRegisterUser_alreadyExistingActiveUser
        $this->email->subject('Someone tried to create an account with your address')
        ->html($this->mailer->getContentFromTemplate(
            'authentication/email/register-on-existing-locked.php',
            ['user' => $existingUser]
        ))->to(new Address($existingUser->email, $existingUser->name));
        // Send email
        $this->mailer->send($this->email);
    }


}