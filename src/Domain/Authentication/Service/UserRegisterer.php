<?php


namespace App\Domain\Authentication\Service;


use App\Domain\Security\Service\SecurityEmailChecker;
use App\Domain\User\DTO\User;
use App\Domain\User\Service\UserValidator;
use App\Domain\Utility\EmailService;
use App\Infrastructure\Authentication\UserRegistererRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenCreatorRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenDeleterRepository;
use App\Infrastructure\Security\RequestCreatorRepository;
use App\Infrastructure\User\UserDeleterRepository;
use App\Infrastructure\User\UserFinderRepository;

class UserRegisterer
{
    public function __construct(
        private UserValidator $userValidator,
        private SecurityEmailChecker $emailSecurityChecker,
        private UserFinderRepository $userFinderRepository,
        private UserRegistererRepository $userRegistererRepository,
        private RequestCreatorRepository $requestCreatorRepo,
        private UserAlreadyExistingHandler $userAlreadyExistingHandler,
        private VerificationTokenCreator $verificationTokenCreator
    ) { }

    /**
     * Insert user in database
     *
     * @param array $userData
     * @param string|null $captcha user captcha response if filled out
     * @param array $queryParams query params that should be added to email verification link (e.g. redirect)
     *
     * @return int|bool insert id, false if user already exists
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function registerUser(array $userData, string|null $captcha = null, array $queryParams = []): bool|int
    {
        $user = new User($userData, true);

        // Validate entries coming from client
        $this->userValidator->validateUserRegistration($user);

        $this->emailSecurityChecker->performEmailAbuseCheck($user->email, $captcha);

        $existingUser = $this->userFinderRepository->findUserByEmail($user->email);
        // Check if user already exists
        if ($existingUser->email !== null) {
            return $this->userAlreadyExistingHandler->handleAlreadyExistingUser($existingUser);
        }

        $user->passwordHash = password_hash($user->password, PASSWORD_DEFAULT);

        // Set default status and role
        $user->status = User::STATUS_UNVERIFIED;
        $user->role = 'user';

        // Insert new user into database
        $user->id = $this->userRegistererRepository->insertUser($user);

        // Create, insert and send token to user
        $this->verificationTokenCreator->createAndSendUserVerification($user, $queryParams);

        $this->requestCreatorRepo->insertEmailRequest($user->email, $_SERVER['REMOTE_ADDR']);

        return $user->id;
    }
}