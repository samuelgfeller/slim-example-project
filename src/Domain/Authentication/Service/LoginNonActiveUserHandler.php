<?php


namespace App\Domain\Authentication\Service;

use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Data\UserData;
use Psr\Log\LoggerInterface;

/**
 * Logic on cases where user tries to log in but his status is not active
 * In separate class to not overload LoginVerifier
 */
class LoginNonActiveUserHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private VerificationTokenCreator $verificationTokenCreator,
        private LoginMailer $loginMailer,
        LoggerFactory $logger
    )
    {
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('auth-register-already-existing');
    }

    /**
     * When user tries to login but his status is unverified
     *
     * @param UserData $user
     * @return void
     */
    public function handleUnverifiedUser(UserData $user, array $queryParams = []): void
    {
        $queryParams = $this->verificationTokenCreator->createUserVerification($user, $queryParams);
        $this->loginMailer->sendInfoToUnverifiedUser($user, $queryParams);
    }


}