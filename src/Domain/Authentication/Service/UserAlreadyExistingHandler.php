<?php


namespace App\Domain\Authentication\Service;

use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Data\UserData;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenDeleterRepository;
use App\Infrastructure\Security\RequestCreatorRepository;
use App\Infrastructure\User\UserDeleterRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;

class UserAlreadyExistingHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private UserDeleterRepository $userDeleterRepository,
        private VerificationTokenDeleterRepository $verificationTokenDeleterRepository,
        private RegistrationMailer $mailer,
        LoggerFactory $logger
    )
    {
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('auth-register-already-existing');
    }

    public function handleUnverifiedExistingUser(UserData $existingUser): void
    {
        // Soft delete user so that new one can be inserted properly
        $this->userDeleterRepository->deleteUserById($existingUser->id);
        $this->verificationTokenDeleterRepository->deleteVerificationToken($existingUser->id);
    }

    /**
     * Logic when user already exists and is not unverified meaning either
     *  active, locked or suspended during registration
     *
     * @param UserData $existingUser
     *
     * @return bool
     */
    public function handleVerifiedExistingUser(UserData $existingUser): bool
    {
        if ($existingUser->status === UserData::STATUS_SUSPENDED) {
            // Todo inform user (only via mail) that he is suspended and isn't allowed to create a new account
            try {
                $this->mailer->sendRegisterExistingSuspendedUser($existingUser);
            } catch (TransportException $e) {
                // We try to hide if an email already exists or not so if email fails, nothing is done
                $this->logger->error($e->getMessage());
            } catch (\Throwable $e) { // For phpRenderer ->fetch()
                $this->logger->error($e->getMessage());
            }
            return false;
        }

        if ($existingUser->status === UserData::STATUS_LOCKED) {
            try {
                $this->mailer->sendRegisterExistingLockedUser($existingUser);

            } catch (TransportException $e) {
                // We try to hide if an email already exists or not so if email fails, nothing is done
                $this->logger->error($e->getMessage());
            } catch (\Throwable $e) { // For phpRenderer ->fetch()
                $this->logger->error($e->getMessage());
            }
            return false;
        }

        if ($existingUser->status === UserData::STATUS_ACTIVE) {
            try {
                $this->mailer->sendRegisterExistingActiveUser($existingUser);

            } catch (TransportException $e) {
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