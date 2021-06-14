<?php


namespace App\Domain\Authentication\Service;

use App\Domain\Settings;
use App\Domain\User\DTO\User;
use App\Domain\Utility\Mailer;

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

    /**
     * AuthenticationMailer constructor.
     *
     * @param Mailer $mailer
     * @param Settings $settings
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function __construct(
        private Mailer $mailer,
        Settings $settings
    ) {
        $settings = $settings->get('public')['email'];
        // Send auth emails from domain
        $this->mailer->setFrom($settings['main_sender_address'], $settings['main_sender_name']);
        $this->mailer->addReplyTo($settings['main_contact_address']);
    }

    /**
     * Send verification token
     *
     * @param User $user
     * @param array $queryParams
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendRegisterVerificationToken(User $user, array $queryParams): void
    {
        // Send verification mail
        $this->mailer->Subject = 'One more step to register'; // Subject asserted in testRegisterUser
        $this->mailer->setContentFromTemplate(
            'authentication/email/register.email.php',
            ['user' => $user, 'queryParams' => $queryParams]
        );
        $this->mailer->sendTo($user->email, $user->name);
    }

    /**
     * Send email to the already existing user with status "suspended" when someone tries to register with the
     * same email address.
     *
     * @param User $existingUser
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendRegisterExistingActiveUser(User $existingUser): void
    {
        // Send info mail to email address holder
        // Subject asserted in testRegisterUser_alreadyExistingActiveUser
        $this->mailer->Subject = 'Someone tried to create an account with your address';
        $this->mailer->setContentFromTemplate(
            'authentication/email/register-on-existing-active.php',
            ['user' => $existingUser]
        );
        $this->mailer->sendTo($existingUser->email, $existingUser->name);
    }

    /**
     * Send email to the already existing user with status "suspended" when someone tries to register with the
     * same email address.
     *
     * @param User $existingUser
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendRegisterExistingSuspendedUser(User $existingUser): void
    {
        // Send info mail to email address holder
        // Subject asserted in testRegisterUser_alreadyExistingActiveUser
        $this->mailer->Subject = 'Someone tried to create an account with your address';
        $this->mailer->setContentFromTemplate(
            'authentication/email/register-on-existing-suspended.php',
            ['user' => $existingUser]
        );
        $this->mailer->sendTo($existingUser->email, $existingUser->name);
    }

    /**
     * Send email to the already existing user with status "locked" when someone tries to register with the
     * same email address.
     *
     * @param User $existingUser
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendRegisterExistingLockedUser(User $existingUser): void
    {
        // Send info mail to email address holder
        // Subject asserted in testRegisterUser_alreadyExistingActiveUser
        $this->mailer->Subject = 'Someone tried to create an account with your address';
        $this->mailer->setContentFromTemplate(
            'authentication/email/register-on-existing-locked.php',
            ['user' => $existingUser]
        );
        $this->mailer->sendTo($existingUser->email, $existingUser->name);
    }


}