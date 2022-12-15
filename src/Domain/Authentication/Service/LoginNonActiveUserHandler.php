<?php

namespace App\Domain\Authentication\Service;

use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Data\UserData;
use Psr\Log\LoggerInterface;

/**
 * Logic on cases where user tries to log in but his status is not active
 * In separate class to not overload LoginVerifier.
 */
class LoginNonActiveUserHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly VerificationTokenCreator $verificationTokenCreator,
        private readonly LoginMailer $loginMailer,
        LoggerFactory $logger
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('auth-login-non-active-status');
    }

    /**
     * When user tries to log in but his status is unverified.
     *
     * @param UserData $user
     * @param array $queryParams
     *
     * @return void
     */
    public function handleUnverifiedUserLoginAttempt(UserData $user, array $queryParams = []): void
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
     * @return void
     */
    public function handleSuspendedUserLoginAttempt(UserData $user): void
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
     * @return void
     */
    public function handleLockedUserLoginAttempt(UserData $user, array $queryParams = []): void
    {
        // Create verification token to unlock account
        $queryParams = $this->verificationTokenCreator->createUserVerification($user, $queryParams);

        // Send email to locked user
        $this->loginMailer->sendInfoToLockedUser($user, $queryParams);

        // Write event in logger
        $this->logger->info('Login attempt on locked user id ' . $user->id . '.');
    }
}
