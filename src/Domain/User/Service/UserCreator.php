<?php

namespace App\Domain\User\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Authentication\Repository\UserRoleFinderRepository;
use App\Domain\Authentication\Service\RegistrationMailer;
use App\Domain\Authentication\Service\VerificationTokenCreator;
use App\Domain\Security\Service\SecurityEmailChecker;
use App\Domain\User\Data\UserData;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Repository\UserCreatorRepository;
use App\Domain\User\Service\Authorization\UserAuthorizationChecker;
use App\Domain\UserActivity\Service\UserActivityLogger;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final class UserCreator
{
    public function __construct(
        private readonly UserValidator $userValidator,
        private readonly SecurityEmailChecker $emailSecurityChecker,
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
        private readonly UserCreatorRepository $userCreatorRepository,
        private readonly VerificationTokenCreator $verificationTokenCreator,
        private readonly RegistrationMailer $registrationMailer,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly UserActivityLogger $userActivityLogger,
    ) {
    }

    /**
     * User creation logic.
     *
     * @param array $userValues
     * @param string|null $captcha user captcha response if filled out
     * @param array $queryParams query params that should be added to email verification link (e.g. redirect)
     *
     * @throws TransportExceptionInterface|\JsonException|\Exception
     *
     * @return int|bool insert id, false if user already exists
     */
    public function createUser(array $userValues, ?string $captcha = null, array $queryParams = []): bool|int
    {
        // Before validation, check if authenticated user is authorized to create user with the given data
        if ($this->userAuthorizationChecker->isGrantedToCreate($userValues)) {
            // * Validation has to be done AFTER authorization check
            // to not reveal potential sensitive infos such as from the validation messages
            $this->userValidator->validateUserValues($userValues);

            $user = new UserData($userValues);
            // Verify that user (concerned email) or ip address doesn't spam email sending
            $this->emailSecurityChecker->performEmailAbuseCheck($user->email, $captcha);

            $user->passwordHash = password_hash($user->password ?? '', PASSWORD_DEFAULT);

            // Set default status and role
            $user->status = $user->status ?? UserStatus::Unverified;
            $user->userRoleId = $user->userRoleId ??
                $this->userRoleFinderRepository->findUserRoleIdByName(UserRole::NEWCOMER->value);

            // Insert new user into database
            $userRow = $user->toArrayForDatabase();
            $user->id = $this->userCreatorRepository->insertUser($userRow);
            // remove passwords from user row before they are inserted into activity (also id because not relevant)
            unset($userRow['password'], $userRow['password2'], $userRow['password_hash'], $userRow['id'], $userRow['theme']);
            $this->userActivityLogger->logUserActivity(UserActivity::CREATED, 'user', $user->id, $userRow);

            // Create and insert token if unverified
            if ($user->status === UserStatus::Unverified) {
                $queryParams = $this->verificationTokenCreator->createUserVerification($user->id, $queryParams);
                // Send token to user. Mailer errors caught in action
                $this->registrationMailer->sendRegisterVerificationToken(
                    $user->email ?? '',
                    $user->getFullName(),
                    $user->language->value,
                    $queryParams
                );
            }

            return $user->id;
        }
        throw new ForbiddenException('Not allowed to create user.');
    }
}
