<?php

namespace App\Module\Authentication\PasswordReset\Service;

use App\Module\Authentication\PasswordReset\Repository\PasswordChangerRepository;
use App\Module\Authentication\Validation\AuthenticationValidator;
use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\User\Authorization\UserPermissionVerifier;
use App\Module\User\Enum\UserActivity;
use App\Module\UserActivity\Create\Service\UserActivityLogger;
use Psr\Log\LoggerInterface;

final readonly class PasswordChanger
{
    public function __construct(
        private UserPermissionVerifier $userPermissionVerifier,
        private PasswordChangerRepository $passwordChangerRepository,
        private AuthenticationValidator $authenticationValidator,
        private UserActivityLogger $userActivityLogger,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Normal password change with old password, or without (if privilege is high enough).
     *
     * @param $userValues array with 'password', 'password2' and optionally 'old_password'
     * @param int $userId
     *
     * @return bool
     */
    public function changeUserPassword(array $userValues, int $userId): bool
    {
        // Check if password strings are valid
        $this->authenticationValidator->validatePasswordChange($userValues);

        // If an old password is provided and user is allowed to update the password
        if (isset($userValues['old_password'])
            && $this->userPermissionVerifier->isGrantedToUpdate(['password_hash' => 'value'], $userId)
        ) {
            // Verify old password; throws validation exception if old password is incorrect
            $this->authenticationValidator->checkIfOldPasswordIsCorrect(['old_password' => $userValues['old_password']], $userId);
        } // Else if old password is not set, check if granted to update without it.
        if (!isset($userValues['old_password'])
            // Check if allowed to update password without the old password
            && (!$this->userPermissionVerifier->isGrantedToUpdate(['password_without_verification' => 'value'], $userId)
                // Check if allowed to update password_hash of that user
                || !$this->userPermissionVerifier->isGrantedToUpdate(['password_hash' => 'value'], $userId))
        ) {
            throw new ForbiddenException('Not granted to update password without verification.');
        }

        // Calls service function to check if authorized and change password
        return $this->updateUserPassword($userValues['password'], $userId);
    }

    /**
     * Change user password if authorized.
     *
     * @param string $newPassword
     * @param int $userId
     *
     * @return bool
     */
    private function updateUserPassword(string $newPassword, int $userId): bool
    {
        if ($this->userPermissionVerifier->isGrantedToUpdate(['password_hash' => 'value'], $userId)) {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updated = $this->passwordChangerRepository->changeUserPassword($passwordHash, $userId);
            if ($updated) {
                $this->userActivityLogger->logUserActivity(
                    UserActivity::UPDATED,
                    'user',
                    $userId,
                    ['password_hash' => '******']
                );
            }

            return $updated;
        }

        // User does not have the required rights to access area or function
        $this->logger->warning('Failed attempt to change password of user id: ' . $userId);
        throw new ForbiddenException('Not allowed to change password.');
    }
}
