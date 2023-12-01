<?php

namespace App\Domain\Authentication\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Repository\UserUpdaterRepository;
use App\Domain\User\Service\Authorization\UserPermissionVerifier;
use App\Domain\User\Service\UserValidator;
use App\Domain\UserActivity\Service\UserActivityLogger;
use Psr\Log\LoggerInterface;

class PasswordChanger
{
    public function __construct(
        private readonly UserPermissionVerifier $userPermissionVerifier,
        private readonly UserUpdaterRepository $userUpdaterRepository,
        private readonly UserValidator $userValidator,
        private readonly UserActivityLogger $userActivityLogger,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Normal password change with old password or without (if privilege is high enough).
     *
     * @param $userValues array with 'password', 'password2' and optionally 'old_password'
     * @param int $userId
     *
     * @return bool
     */
    public function changeUserPassword(array $userValues, int $userId): bool
    {
        // Check if password strings are valid
        $this->userValidator->validatePasswordChange($userValues);

        // If old password is provided and user is allowed to update password
        if (isset($userValues['old_password'])
            && $this->userPermissionVerifier->isGrantedToUpdate(['password_hash' => 'value'], $userId)
        ) {
            // Verify old password; throws validation exception if not correct old password
            $this->userValidator->checkIfOldPasswordIsCorrect(['old_password' => $userValues['old_password']], $userId);
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
            $updated = $this->userUpdaterRepository->changeUserPassword($passwordHash, $userId);
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

        // User does not have needed rights to access area or function
        $this->logger->warning('Failed attempt to change password of user id: ' . $userId);
        throw new ForbiddenException('Not allowed to change password.');
    }
}
