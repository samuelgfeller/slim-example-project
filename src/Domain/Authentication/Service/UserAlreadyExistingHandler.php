<?php


namespace App\Domain\Authentication\Service;


use App\Application\Actions\Authentication\AuthenticationMailer;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\DTO\User;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenDeleterRepository;
use App\Infrastructure\Security\RequestCreatorRepository;
use App\Infrastructure\User\UserDeleterRepository;
use Psr\Log\LoggerInterface;

class UserAlreadyExistingHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private UserDeleterRepository $userDeleterRepository,
        private VerificationTokenDeleterRepository $verificationTokenDeleterRepository,
        private AuthenticationMailer $mailer,
        private RequestCreatorRepository $requestCreatorRepository,
        LoggerFactory $logger
    )
    {
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('auth-register-already-existing');
    }

    public function handleUnverifiedExistingUser(User $existingUser): void
    {
        // Soft delete user so that new one can be inserted properly
        $this->userDeleterRepository->deleteUserById($existingUser->id);
        $this->verificationTokenDeleterRepository->deleteVerificationToken($existingUser->id);
    }

    /**
     * Logic when user already exists and is not unverified meaning either
     *  active, locked or suspended during registration
     *
     * @param User $existingUser
     *
     * @return bool
     */
    public function handleNotUnverifiedExistingUser(User $existingUser): bool
    {
        if ($existingUser->status === User::STATUS_SUSPENDED) {
            // Todo inform user (only via mail) that he is suspended and isn't allowed to create a new account
            try {
                $this->mailer->sendRegisterExistingSuspendedUser($existingUser);
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                // We try to hide if an email already exists or not so if email fails, nothing is done
                $this->logger->error($e->getMessage());
            } catch (\Throwable $e) { // For phpRenderer ->fetch()
                $this->logger->error($e->getMessage());
            }
            return false;
        }

        if ($existingUser->status === User::STATUS_LOCKED) {
            try {
                $this->mailer->sendRegisterExistingLockedUser($existingUser);

            } catch (\PHPMailer\PHPMailer\Exception $e) {
                // We try to hide if an email already exists or not so if email fails, nothing is done
                $this->logger->error($e->getMessage());
            } catch (\Throwable $e) { // For phpRenderer ->fetch()
                $this->logger->error($e->getMessage());
            }
            return false;
        }

        if ($existingUser->status === User::STATUS_ACTIVE) {
            try {
                $this->mailer->sendRegisterExistingActiveUser($existingUser);

            } catch (\PHPMailer\PHPMailer\Exception $e) {
                // We try to hide if an email already exists or not so if email fails, nothing is done
                $this->logger->error($e->getMessage());
            } catch (\Throwable $e) { // For phpRenderer ->fetch()
                $this->logger->error($e->getMessage());
            }
            return false;
        }

        // todo invalid role in db. Send email to admin to inform that there is something wrong with the user
        throw new \RuntimeException('Invalid role');
    }
}