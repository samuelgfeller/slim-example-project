<?php

namespace App\Module\Authentication\Login\Domain\Service;

use App\Core\Infrastructure\Service\LocaleConfigurator;
use App\Module\Authentication\Login\Domain\Exception\UnableToLoginStatusNotActiveException;
use App\Module\Authentication\TokenVerification\Service\VerificationTokenCreator;
use App\Module\Security\Domain\Service\SecurityEmailChecker;
use App\Module\User\Data\UserData;
use App\Module\User\Enum\UserStatus;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Logic cases where a user tries to log in but his status is not active
 * Methods in this separate class to not overload LoginVerifier.
 */
final readonly class LoginNonActiveUserHandler
{
    public function __construct(
        private VerificationTokenCreator $verificationTokenCreator,
        private LoginMailSender $loginMailer,
        private LocaleConfigurator $localeConfigurator,
        private SecurityEmailChecker $securityEmailChecker,
        private LoggerInterface $logger,
        private AuthenticationLogger $authenticationLogger,
    ) {
    }

    /**
     * Handles the login attempt from a non-active user.
     * Sends an email to the user with information and a link to activate their account.
     *
     * @param UserData $dbUser the user data from the database
     * @param array $queryParams the query parameters
     * @param ?string $captcha
     *
     * @throws \RuntimeException if there is an invalid status in the database
     * @throws UnableToLoginStatusNotActiveException thrown in all cases as the user status is not active
     * @throws TransportExceptionInterface if there is an exception while sending an email
     *
     * @return void
     */
    public function handleLoginAttemptFromNonActiveUser(
        UserData $dbUser,
        array $queryParams,
        ?string $captcha = null,
    ): void {
        // DTO values may be null and these values are required
        if (!isset($dbUser->id, $dbUser->email)) {
            throw new \RuntimeException('User details not found');
        }
        $this->securityEmailChecker->performEmailAbuseCheck($dbUser->email, $captcha);
        // If status not active, create exception object
        $unableToLoginException = new UnableToLoginStatusNotActiveException(
            __('Unable to login at the moment, please check your email inbox for a more detailed message.')
        );

        // Log failed login attempt
        $this->authenticationLogger->logLoginRequest($dbUser->email, false, $dbUser->id);

        $originalLocale = setlocale(LC_ALL, 0);

        try {
            $userId = $dbUser->id;
            $email = $dbUser->email;
            $fullName = $dbUser->getFullName();

            // Change language to the one the user selected in settings (in case it differs from browser lang)
            $this->localeConfigurator->setLanguage($dbUser->language->value);

            if ($dbUser->status === UserStatus::Unverified) {
                // Inform user via email that account is unverified, and he should click on the link in his inbox
                $this->handleUnverifiedUserLoginAttempt($userId, $email, $fullName, $queryParams);
                // Throw exception to display error message in form
                throw $unableToLoginException;
            }

            if ($dbUser->status === UserStatus::Suspended) {
                // Inform the user (only via mail) that they are suspended
                $this->handleSuspendedUserLoginAttempt($userId, $email, $fullName);
                // Throw exception to display error message in form
                throw $unableToLoginException;
            }

            if ($dbUser->status === UserStatus::Locked) {
                // login fail and inform user (only via mail) that they are locked and provide unlock token
                $this->handleLockedUserLoginAttempt($userId, $email, $fullName, $queryParams);
                // Throw exception to display error message in form
                throw $unableToLoginException;
            }
        } catch (TransportException $transportException) {
            // If exception is thrown reset locale as well. If $unableToLoginException
            $this->localeConfigurator->setLanguage($originalLocale);
            // Exception while sending email
            throw new UnableToLoginStatusNotActiveException(
                'Unable to login at the moment and there was an error when sending an email to you.' .
                "\n Please report the error."
            );
        } // Catch exception to reset locale before throwing it again to be caught in the action
        catch (UnableToLoginStatusNotActiveException $unableToLoginStatusNotActiveException) {
            // Reset locale
            $this->localeConfigurator->setLanguage($originalLocale);
            throw $unableToLoginStatusNotActiveException;
        }

        throw new \RuntimeException('Invalid status');
    }

    /**
     * When a user tries to log in but their status is unverified.
     *
     * @param int $userId
     * @param string $email
     * @param string $fullName
     * @param array $queryParams
     *
     * @throws TransportExceptionInterface
     *
     * @return void
     */
    private function handleUnverifiedUserLoginAttempt(
        int $userId,
        string $email,
        string $fullName,
        array $queryParams = [],
    ): void {
        // Create a verification token, so they don't have to register again
        $queryParams = $this->verificationTokenCreator->createUserVerification($userId, $queryParams);
        $this->loginMailer->sendInfoToUnverifiedUser($email, $fullName, $queryParams);

        // Write event in logger
        $this->logger->info('Login attempt on unverified user id ' . $userId . '.');
    }

    /**
     * When a user tries to log in but their status is suspended.
     *
     * @param int $userId
     * @param string $email
     * @param string $fullName
     *
     * @throws TransportExceptionInterface
     *
     * @return void
     */
    private function handleSuspendedUserLoginAttempt(int $userId, string $email, string $fullName): void
    {
        // Send email to suspended user
        $this->loginMailer->sendInfoToSuspendedUser($email, $fullName);

        // Write event in logger
        $this->logger->info('Login attempt on suspended user id ' . $userId . '.');
    }

    /**
     * When a user tries to log in, but their status is locked
     * which can happen on too many failed login requests.
     *
     * @param int $userId
     * @param string $email
     * @param string $fullName
     * @param array $queryParams existing query params like redirect
     *
     * @throws TransportExceptionInterface
     *
     * @return void
     */
    private function handleLockedUserLoginAttempt(
        int $userId,
        string $email,
        string $fullName,
        array $queryParams = [],
    ): void {
        // Create verification token to unlock account
        $queryParams = $this->verificationTokenCreator->createUserVerification($userId, $queryParams);

        // Send email to locked user
        $this->loginMailer->sendInfoToLockedUser($email, $fullName, $queryParams);

        // Write event in logger
        $this->logger->info('Login attempt on locked user id ' . $userId . '.');
    }
}
