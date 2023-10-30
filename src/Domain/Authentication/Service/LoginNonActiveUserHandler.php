<?php

namespace App\Domain\Authentication\Service;

use App\Common\LocaleHelper;
use App\Domain\Authentication\Exception\UnableToLoginStatusNotActiveException;
use App\Domain\Factory\Infrastructure\LoggerFactory;
use App\Domain\Security\Service\SecurityEmailChecker;
use App\Domain\Settings;
use App\Domain\User\Data\UserData;
use App\Domain\User\Enum\UserStatus;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Logic on cases where user tries to log in but his status is not active
 * In separate class to not overload LoginVerifier.
 */
class LoginNonActiveUserHandler
{
    private LoggerInterface $logger;
    private string $mainContactEmail;

    public function __construct(
        private readonly VerificationTokenCreator $verificationTokenCreator,
        private readonly LoginMailer $loginMailer,
        private readonly LocaleHelper $localeHelper,
        private readonly SecurityEmailChecker $securityEmailChecker,
        readonly Settings $settings,
        LoggerFactory $logger
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createLogger('auth-login-non-active-status');
        $this->mainContactEmail = $this->settings->get(
            'public'
        )['email']['main_contact_address'] ?? 'slim-example-project@samuel-gfeller.ch';
    }

    /**
     * Handles the login attempt from a non-active user.
     * Sends an email to the user with information and a link to activate his account.
     *
     * @param UserData $dbUser the user data from the database
     * @param array $queryParams the query parameters
     * @param ?string $captcha
     *
     * @throws TransportExceptionInterface if there is an exception while sending an email
     * @throws \RuntimeException if there is an invalid status in the database
     * @throws UnableToLoginStatusNotActiveException thrown in all cases as the user status is not active
     *
     * @return void
     */
    public function handleLoginAttemptFromNonActiveUser(
        UserData $dbUser,
        array $queryParams,
        ?string $captcha = null
    ): void {
        $this->securityEmailChecker->performEmailAbuseCheck($dbUser->email, $captcha);
        // If status not active, create exception object
        $unableToLoginException = new UnableToLoginStatusNotActiveException(
            __('Unable to login at the moment, please check your email inbox for a more detailed message.')
        );
        try {
            // Change language to the one the user selected in settings (in case it differs from browser lang)
            $originalLocale = setlocale(LC_ALL, 0);
            $this->localeHelper->setLanguage($dbUser->language->value);

            if ($dbUser->status === UserStatus::Unverified) {
                // Inform user via email that account is unverified, and he should click on the link in his inbox
                $this->handleUnverifiedUserLoginAttempt($dbUser, $queryParams);
                // Throw exception to display error message in form
                throw $unableToLoginException;
            }

            if ($dbUser->status === UserStatus::Suspended) {
                // Inform user (only via mail) that he is suspended
                $this->handleSuspendedUserLoginAttempt($dbUser);
                // Throw exception to display error message in form
                throw $unableToLoginException;
            }

            if ($dbUser->status === UserStatus::Locked) {
                // login fail and inform user (only via mail) that he is locked and provide unlock token
                $this->handleLockedUserLoginAttempt($dbUser, $queryParams);
                // Throw exception to display error message in form
                throw $unableToLoginException;
            }
            // Reset locale if sending the mail was successful
            $this->localeHelper->setLanguage($originalLocale);
        } catch (TransportException $transportException) {
            // If exception is thrown reset locale as well. If $unableToLoginException
            $this->localeHelper->setLanguage($originalLocale);
            // Exception while sending email
            throw new UnableToLoginStatusNotActiveException(
                'Unable to login at the moment and there was an error when sending an email to you.' .
                "\n Please contact $this->mainContactEmail."
            );
        } // Catch exception to reset locale before throwing it again to be caught in the action
        catch (UnableToLoginStatusNotActiveException $unableToLoginStatusNotActiveException) {
            // Reset locale
            $this->localeHelper->setLanguage($originalLocale);
            throw $unableToLoginStatusNotActiveException;
        }

        // todo invalid status in db. Send email to admin to inform that there is something wrong with the user
        throw new \RuntimeException('Invalid status');
    }

    /**
     * When user tries to log in but his status is unverified.
     *
     * @param UserData $user
     * @param array $queryParams
     *
     * @throws TransportExceptionInterface
     *
     * @return void
     */
    private function handleUnverifiedUserLoginAttempt(UserData $user, array $queryParams = []): void
    {
        // Create verification token, so he doesn't have to register again
        $queryParams = $this->verificationTokenCreator->createUserVerification($user, $queryParams);
        $this->loginMailer->sendInfoToUnverifiedUser($user, $queryParams);

        // Write event in logger
        $this->logger->info('Login attempt on unverified user id ' . $user->id . '.');
    }

    /**
     * When user tries to log in but his status is suspended.
     *
     * @param UserData $user
     *
     * @throws TransportExceptionInterface
     *
     * @return void
     */
    private function handleSuspendedUserLoginAttempt(UserData $user): void
    {
        // Send email to suspended user
        $this->loginMailer->sendInfoToSuspendedUser($user);

        // Write event in logger
        $this->logger->info('Login attempt on suspended user id ' . $user->id . '.');
    }

    /**
     * When user tries to log in but his status is locked
     * which can happen on too many failed login requests.
     *
     * @param UserData $user
     * @param array $queryParams existing query params like redirect
     *
     * @throws TransportExceptionInterface
     *
     * @return void
     */
    private function handleLockedUserLoginAttempt(UserData $user, array $queryParams = []): void
    {
        // Create verification token to unlock account
        $queryParams = $this->verificationTokenCreator->createUserVerification($user, $queryParams);

        // Send email to locked user
        $this->loginMailer->sendInfoToLockedUser($user, $queryParams);

        // Write event in logger
        $this->logger->info('Login attempt on locked user id ' . $user->id . '.');
    }
}
