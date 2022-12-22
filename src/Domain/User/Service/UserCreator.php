<?php

namespace App\Domain\User\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Authentication\Service\RegistrationMailer;
use App\Domain\Authentication\Service\VerificationTokenCreator;
use App\Domain\Security\Service\SecurityEmailChecker;
use App\Domain\User\Authorization\UserAuthorizationChecker;
use App\Domain\User\Data\UserData;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Enum\UserStatus;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\User\UserCreatorRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class UserCreator
{
    public function __construct(
        private readonly UserValidator $userValidator,
        private readonly SecurityEmailChecker $emailSecurityChecker,
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
        private readonly UserCreatorRepository $userCreatorRepository,
        private readonly VerificationTokenCreator $verificationTokenCreator,
        private readonly RegistrationMailer $registrationMailer,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly UserActivityManager $userActivityManager,
    ) {
    }

    /**
     * Insert user in database.
     *
     * @param array $userValues
     * @param string|null $captcha user captcha response if filled out
     * @param array $queryParams query params that should be added to email verification link (e.g. redirect)
     *
     * @throws TransportExceptionInterface|\JsonException|\Exception
     *
     * @return int|bool insert id, false if user already exists
     */
    public function createUser(array $userValues, string|null $captcha = null, array $queryParams = []): bool|int
    {
        $user = new UserData($userValues);

        // Check if authenticated user is authorized to create user with the given data
        // Has to be at the top to not reveal potential sensitive infos such as from the validation
        if ($this->userAuthorizationChecker->isGrantedToCreate($user)) {
            // Validate entries coming from client
            $this->userValidator->validateUserCreation($user);

            // Verify that user (concerned email) or ip address doesn't spam email sending
            $this->emailSecurityChecker->performEmailAbuseCheck($user->email, $captcha);

            $user->passwordHash = password_hash($user->password, PASSWORD_DEFAULT);

            // Set default status and role
            $user->status = $user->status ?? UserStatus::Unverified;
            $user->userRoleId = $user->userRoleId ??
                $this->userRoleFinderRepository->findUserRoleIdByName(UserRole::NEWCOMER->value);

            // Insert new user into database
            $userRow = $user->toArrayForDatabase();
            $user->id = $this->userCreatorRepository->insertUser($userRow);
            // remove passwords from user row before they are inserted into activity (also id because not relevant)
            unset($userRow['password'], $userRow['password2'], $userRow['password_hash'], $userRow['id']);
            $this->userActivityManager->addUserActivity(UserActivity::CREATED, 'user', $user->id, $userRow);

            // Create and insert token if unverified
            if ($user->status === UserStatus::Unverified) {
                $queryParams = $this->verificationTokenCreator->createUserVerification($user, $queryParams);
                // Send token to user. Mailer errors caught in action
                $this->registrationMailer->sendRegisterVerificationToken($user, $queryParams);
            }

            return $user->id;
        }
        throw new ForbiddenException('Not allowed to create user.');
    }
}
