<?php

namespace App\Module\User\Create\Service;

use App\Module\Authentication\Register\Service\RegistrationMailSender;
use App\Module\Authentication\TokenVerification\Service\VerificationTokenCreator;
use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\Security\Email\Service\SecurityEmailChecker;
use App\Module\User\Create\Repository\UserCreatorRepository;
use App\Module\User\Data\UserData;
use App\Module\User\Enum\UserActivity;
use App\Module\User\Enum\UserRole;
use App\Module\User\Enum\UserStatus;
use App\Module\User\Validation\Service\UserValidator;
use App\Module\UserActivity\Log\Service\UserActivityLogger;
use App\Module\UserRole\Find\Repository\UserRoleFinderRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final readonly class UserCreator
{
    public function __construct(
        private UserValidator $userValidator,
        private SecurityEmailChecker $emailSecurityChecker,
        private UserCreateAuthorizationChecker $userCreateAuthorizationChecker,
        private UserCreatorRepository $userCreatorRepository,
        private VerificationTokenCreator $verificationTokenCreator,
        private RegistrationMailSender $registrationMailer,
        private UserRoleFinderRepository $userCreateRoleFinderRepository,
        private UserActivityLogger $userActivityLogger,
    ) {
    }

    /**
     * User creation logic.
     *
     * @param array $userValues
     *
     * @throws TransportExceptionInterface|\JsonException|\Exception
     *
     * @return int|bool insert id, false if user already exists
     */
    public function createUser(array $userValues): bool|int
    {
        // Before validation, check if authenticated user is authorized to create user with the given data
        if ($this->userCreateAuthorizationChecker->isGrantedToCreate($userValues)) {
            // * Validation has to be done AFTER authorization check
            // to not reveal potential sensitive infos such as from the validation messages (e.g. email already exists)
            $this->userValidator->validateUserValues($userValues);

            $user = new UserData($userValues);
            // Verify that user (concerned email) or ip address doesn't spam email sending
            $this->emailSecurityChecker->performEmailAbuseCheck(
                $user->email,
                $userValues['g-recaptcha-response'] ?? null
            );

            $user->passwordHash = password_hash($user->password ?? '', PASSWORD_DEFAULT);

            // Set default status and role
            $user->status = $user->status ?? UserStatus::Unverified;
            $user->userRoleId = $user->userRoleId ??
                $this->userCreateRoleFinderRepository->findUserRoleIdByName(UserRole::NEWCOMER->value);

            // Insert new user into database
            $userRow = $user->toArrayForDatabase();
            $user->id = $this->userCreatorRepository->insertUser($userRow);
            // remove passwords from user row before they are inserted into activity (also id because not relevant)
            unset($userRow['password'], $userRow['password2'], $userRow['password_hash'], $userRow['id'], $userRow['theme']);
            $this->userActivityLogger->logUserActivity(UserActivity::CREATED, 'user', $user->id, $userRow);

            // Create and insert token if unverified
            if ($user->status === UserStatus::Unverified) {
                $queryParams = $this->verificationTokenCreator->createUserVerification($user->id);
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
