<?php

namespace App\Domain\Authentication\Service;

use App\Domain\Exceptions\DomainRecordNotFoundException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Security\Service\SecurityEmailChecker;
use App\Domain\Settings;
use App\Domain\User\Data\UserData;
use App\Domain\User\Service\UserValidator;
use App\Domain\Utility\Mailer;
use App\Infrastructure\User\UserFinderRepository;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * This class manages the emails sent in relation to login.
 */
class PasswordRecoveryEmailSender
{
    private Email $email;

    /**
     * LoginMailer constructor.
     *
     * @param Mailer $mailer email sender and helper
     * @param Settings $settings
     */
    public function __construct(
        private Mailer $mailer,
        private UserValidator $userValidator,
        private UserFinderRepository $userFinderRepository,
        private VerificationTokenCreator $verificationTokenCreator,
        Settings $settings,
        private SecurityEmailChecker $securityEmailChecker,
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
     * When user requests a new password for email
     *
     * @param array $userData
     * @throws ValidationException
     */
    public function sendPasswordRecoveryEmail(array $userData, string|null $captcha = null): void
    {
        $user = new UserData($userData);

        $this->userValidator->validatePasswordResetEmail($user);

        // Verify that user (concerned email) or ip address doesn't spam email sending
        $this->securityEmailChecker->performEmailAbuseCheck($user->email, $captcha);

        $dbUser = $this->userFinderRepository->findUserByEmail($user->email);

        if ($dbUser->email !== null) {
            // Create verification token, so he doesn't have to register again
            $queryParamsWithToken = $this->verificationTokenCreator->createUserVerification($dbUser);

            // Send verification mail
            $this->email->subject('Reset password')->html(
                    $this->mailer->getContentFromTemplate('authentication/email/password-reset.email.php',
                        ['user' => $dbUser, 'queryParams' => $queryParamsWithToken])
                )->to(new Address($dbUser->email, $dbUser->getFullName()));
            // Send email
            $this->mailer->send($this->email);
            return;
        }

        throw new DomainRecordNotFoundException('User not existing');
    }

}