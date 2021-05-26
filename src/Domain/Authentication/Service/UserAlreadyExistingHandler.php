<?php


namespace App\Domain\Authentication\Service;


use App\Domain\Factory\LoggerFactory;
use App\Domain\User\DTO\User;
use App\Domain\Utility\EmailService;
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
        private EmailService $emailService,
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
            return false;
        }

        if ($existingUser->status === User::STATUS_LOCKED) {
            // Todo inform user (only via mail) that he is locked and can't create a new account
            return false;
        }

        if ($existingUser->status === User::STATUS_ACTIVE) {
            try {
                // Send info mail to email address holder
                // Subject asserted in testRegisterUser_alreadyExistingActiveUser
                $this->emailService->setSubject('Someone tried to create an account with your address');
                $this->emailService->setContentFromTemplate(
                    'Authentication/register-on-existing.email.php',
                    ['user' => $existingUser]
                );
                $this->emailService->setFrom('slim-example-project@samuel-gfeller.ch', 'Slim Example Project');
                $this->emailService->sendTo($existingUser->email, $existingUser->name);
                $this->requestCreatorRepository->insertEmailRequest($existingUser->email, $_SERVER['REMOTE_ADDR']);
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