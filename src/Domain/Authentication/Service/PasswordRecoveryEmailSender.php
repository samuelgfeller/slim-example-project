<?php

namespace App\Domain\Authentication\Service;

use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Security\Service\SecurityEmailChecker;
use App\Domain\User\Repository\UserFinderRepository;
use App\Infrastructure\Service\LocaleConfigurator;
use App\Infrastructure\Service\Mailer;
use App\Infrastructure\Utility\Settings;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * This class manages the emails sent in relation to login.
 */
final readonly class PasswordRecoveryEmailSender
{
    private Email $email;

    public function __construct(
        private Mailer $mailer,
        private AuthenticationValidator $authenticationValidator,
        private UserFinderRepository $userFinderRepository,
        private VerificationTokenCreator $verificationTokenCreator,
        Settings $settings,
        private SecurityEmailChecker $securityEmailChecker,
        private LocaleConfigurator $localeConfigurator,
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
     * When a user requests a new password.
     *
     * @param array $userValues
     *
     * @throws ValidationException
     * @throws DomainRecordNotFoundException
     * @throws TransportExceptionInterface
     */
    public function sendPasswordRecoveryEmail(array $userValues): void
    {
        $this->authenticationValidator->validatePasswordResetEmail($userValues);

        // Verify that user (concerned email) doesn't spam email sending
        $this->securityEmailChecker->performEmailAbuseCheck(
            $userValues['email'],
            $userValues['g-recaptcha-response'] ?? null
        );

        $dbUser = $this->userFinderRepository->findUserByEmail($userValues['email']);

        if (isset($dbUser->email, $dbUser->id)) {
            // Create a verification token, so they don't have to register again
            $queryParamsWithToken = $this->verificationTokenCreator->createUserVerification($dbUser->id);

            // Change language to one the user chose in settings
            $originalLocale = setlocale(LC_ALL, 0);
            $this->localeConfigurator->setLanguage($dbUser->language->value);

            // Send verification mail
            $this->email->subject(__('Reset password'))->html(
                $this->mailer->getContentFromTemplate(
                    'authentication/email/' . $this->localeConfigurator->getLanguageCodeForPath() .
                    'password-reset.email.php',
                    ['user' => $dbUser, 'queryParams' => $queryParamsWithToken]
                )
            )->to(new Address($dbUser->email, $dbUser->getFullName()));
            // Send email
            $this->mailer->send($this->email);
            // Reset locale to browser language
            $this->localeConfigurator->setLanguage($originalLocale);

            // User activity entry is done when a user verification token is created
            return;
        }

        throw new DomainRecordNotFoundException('User not existing');
    }
}
