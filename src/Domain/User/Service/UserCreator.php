<?php


namespace App\Domain\User\Service;


use App\Domain\Authentication\Service\RegistrationMailer;
use App\Domain\Authentication\Service\UserAlreadyExistingHandler;
use App\Domain\Authentication\Service\VerificationTokenCreator;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Security\Service\SecurityEmailChecker;
use App\Domain\User\Authorization\UserAuthorizationChecker;
use App\Domain\User\Data\UserData;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Enum\UserStatus;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\User\UserCreatorRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class UserCreator
{
    public function __construct(
        private UserValidator $userValidator,
        private SecurityEmailChecker $emailSecurityChecker,
        private UserAuthorizationChecker $userAuthorizationChecker,
        private UserCreatorRepository $userCreatorRepository,
        private UserAlreadyExistingHandler $userAlreadyExistingHandler,
        private VerificationTokenCreator $verificationTokenCreator,
        private RegistrationMailer $registrationMailer,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
    ) {
    }

    /**
     * Insert user in database
     *
     * @param array $userValues
     * @param string|null $captcha user captcha response if filled out
     * @param array $queryParams query params that should be added to email verification link (e.g. redirect)
     *
     * @return int|bool insert id, false if user already exists
     * @throws TransportExceptionInterface
     */
    public function createUser(array $userValues, string|null $captcha = null, array $queryParams = []): bool|int
    {
        $user = new UserData($userValues, true);

        // Validate entries coming from client
        $this->userValidator->validateUserCreation($user);

        // Verify that user (concerned email) or ip address doesn't spam email sending
        $this->emailSecurityChecker->performEmailAbuseCheck($user->email, $captcha);

        // Check if user with same email already exists
        // $existingUser = $this->userFinderRepository->findUserByEmail($user->email);
        // if ($existingUser->email !== null) {
        //     // If unverified and registered again, old user should be deleted and replaced with new input and verification
        //     // Reason: User could have lost the email or someone else tried to register under someone else's name before
        //     if ($existingUser->status === UserStatus::UNVERIFIED) {
        //         // Only delete the user and token but not return as function should continue normally and insert new user
        //         $this->userAlreadyExistingHandler->handleUnverifiedExistingUser($existingUser);
        //     }else {
        //         return $this->userAlreadyExistingHandler->handleVerifiedExistingUser($existingUser);
        //     }
        // }

        $user->passwordHash = password_hash($user->password, PASSWORD_DEFAULT);

        // Set default status and role
        $user->status = $user->status ?? UserStatus::Unverified;
        $user->userRoleId = $user->userRoleId ??
            $this->userRoleFinderRepository->findUserRoleIdByName(UserRole::NEWCOMER->value);

        // Check if authenticated user is authorized to create user with the given data
        if ($this->userAuthorizationChecker->isGrantedToCreate($user)) {
            // Insert new user into database
            $user->id = $this->userCreatorRepository->insertUser($user);
            // Create and insert token
            $queryParams = $this->verificationTokenCreator->createUserVerification($user, $queryParams);
            // Send token to user. Mailer errors caught in action
            $this->registrationMailer->sendRegisterVerificationToken($user, $queryParams);

            return $user->id;
        }
        throw new ForbiddenException('Not allowed to create user.');
    }
}