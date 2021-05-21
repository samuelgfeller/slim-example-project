<?php


namespace App\Domain\Authentication\Service;


use App\Domain\User\DTO\User;
use App\Domain\Utility\EmailService;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenDeleterRepository;
use App\Infrastructure\Security\RequestCreatorRepository;
use App\Infrastructure\User\UserDeleterRepository;

class UserAlreadyExistingHandler
{
    public function __construct(
        private UserDeleterRepository $userDeleterRepository,
        private VerificationTokenDeleterRepository $verificationTokenDeleterRepository,
        private EmailService $emailService,
        private RequestCreatorRepository $requestCreatorRepository
    )
    {
    }

    /**
     * Logic when user already exists during registration
     *
     * @param User $existingUser
     *
     * @return false
     */
    public function handleAlreadyExistingUser(User $existingUser): bool
    {
        // If unverified and registered again, old user should be deleted and replaced with new input and verification
        // Reason: User could have lost the email or someone else tried to register under someone elses name
        if ($existingUser->status === User::STATUS_UNVERIFIED) {
            // Soft delete user so that new one can be inserted properly
            $this->userDeleterRepository->deleteUserById($existingUser->id);
            $this->verificationTokenDeleterRepository->deleteVerificationToken($existingUser->id);
        } elseif ($existingUser->status === User::STATUS_SUSPENDED) {
            // Todo inform user (only via mail) that he is suspended and isn't allowed to create a new account
            return false;
        } elseif ($existingUser->status === User::STATUS_LOCKED) {
            // Todo inform user (only via mail) that he is locked and can't create a new account
            return false;
        } elseif ($existingUser->status === User::STATUS_ACTIVE) {
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
            } catch (\Throwable $e) { // For phpRenderer ->fetch()
            }
            return false;
        } else {
            // todo invalid role in db. Send email to admin to inform that there is something wrong with the user
            throw new \RuntimeException('Invalid role');
        }
    }
}