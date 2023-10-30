<?php

namespace App\Domain\Authentication\Service;

use App\Common\LocaleHelper;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\Security\Service\SecurityEmailChecker;
use App\Domain\Service\Mailer;
use App\Domain\User\Repository\UserFinderRepository;
use App\Domain\User\Service\UserValidator;
use App\Domain\Utility\Settings;
use App\Domain\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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
     * @param UserValidator $userValidator
     * @param UserFinderRepository $userFinderRepository
     * @param VerificationTokenCreator $verificationTokenCreator
     * @param Settings $settings
     * @param SecurityEmailChecker $securityEmailChecker
     * @param LocaleHelper $localeHelper
     */
    public function __construct(
        private readonly Mailer $mailer,
        private readonly UserValidator $userValidator,
        private readonly UserFinderRepository $userFinderRepository,
        private readonly VerificationTokenCreator $verificationTokenCreator,
        Settings $settings,
        private readonly SecurityEmailChecker $securityEmailChecker,
        private readonly LocaleHelper $localeHelper,
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
     * When user requests a new password for email.
     *
     * @param array $userValues
     * @param string|null $captcha
     *
     * @throws ValidationException|TransportExceptionInterface
     */
    public function sendPasswordRecoveryEmail(array $userValues, ?string $captcha = null): void
    {
        $this->userValidator->validatePasswordResetEmail($userValues);
        $email = $userValues['email'];

        // Verify that user (concerned email) or ip address doesn't spam email sending
        $this->securityEmailChecker->performEmailAbuseCheck($email, $captcha);

        $dbUser = $this->userFinderRepository->findUserByEmail($email);

        if ($dbUser->email !== null) {
            // Create verification token, so he doesn't have to register again
            $queryParamsWithToken = $this->verificationTokenCreator->createUserVerification($dbUser);

            // Change language to one the user chose in settings
            $originalLocale = setlocale(LC_ALL, 0);
            $this->localeHelper->setLanguage($dbUser->language->value);

            // Send verification mail
            $this->email->subject(__('Reset password'))->html(
                $this->mailer->getContentFromTemplate(
                    'authentication/email/' . $this->localeHelper->getLanguageCodeForPath() .
                    'password-reset.email.php',
                    ['user' => $dbUser, 'queryParams' => $queryParamsWithToken]
                )
            )->to(new Address($dbUser->email, $dbUser->getFullName()));
            // Send email
            $this->mailer->send($this->email);
            // Reset locale
            $this->localeHelper->setLanguage($originalLocale);

            // User activity entry is done when user verification token is created
            return;
        }

        throw new DomainRecordNotFoundException('User not existing');
    }
}
