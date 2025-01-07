<?php

namespace App\Module\User\Service;

use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\Exception\Domain\InvalidOperationException;
use App\Module\User\Authorization\UserPermissionVerifier;
use App\Module\User\Enum\UserActivity;
use App\Module\User\Repository\UserUpdaterRepository;
use App\Module\UserActivity\Service\UserActivityLogger;
use Psr\Log\LoggerInterface;

final readonly class UserUpdater
{
    public function __construct(
        private UserValidator $userValidator,
        private UserPermissionVerifier $userPermissionVerifier,
        private UserUpdaterRepository $userUpdaterRepository,
        private UserActivityLogger $userActivityLogger,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Update user values.
     * This function is intended for changes coming from a user,
     * so it changes only "user changeable" general info (not password).
     *
     * @param int $userIdToChange user id on which the change is requested to be made
     * @param array $userValues values to change
     *
     * @return bool
     */
    public function updateUser(int $userIdToChange, array $userValues): bool
    {
        // Add user id to user values as it's needed in the validator
        $userValues['id'] = $userIdToChange;
        $this->userValidator->validateUserValues($userValues, false);

        // Unset id from $userValues as this array will be used to update the user and id won't be changed
        unset($userValues['id']);

        // Check if it's admin or if it's its own user
        if ($this->userPermissionVerifier->isGrantedToUpdate($userValues, $userIdToChange)) {
            // User values to change (cannot use object as unset values would be "null" and remove values in db)
            $validUpdateData = [];
            // Additional check to be sure that only columns that may be updated are sent to the database
            foreach ($userValues as $column => $value) {
                // Check that keys are one of the database columns that may be updated
                if (in_array($column, [
                    'first_name',
                    'last_name',
                    'email',
                    'status',
                    'user_role_id',
                    'theme',
                    'language',
                ], true)) {
                    $validUpdateData[$column] = $value;
                } else {
                    throw new InvalidOperationException('Not allowed to change user column ' . $column);
                }
            }
            $updated = $this->userUpdaterRepository->updateUser($userIdToChange, $validUpdateData);
            if ($updated) {
                $this->userActivityLogger->logUserActivity(
                    UserActivity::UPDATED,
                    'user',
                    $userIdToChange,
                    $validUpdateData
                );
            }

            return $updated;
        }

        // User does not have needed rights to access area or function
        $this->logger->notice(
            'User tried to update other user with id: ' . $userIdToChange
        );
        throw new ForbiddenException('Not allowed to update user.');
    }
}
