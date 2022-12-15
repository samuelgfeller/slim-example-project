<?php

namespace App\Domain\User\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Client\Exception\NotAllowedException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Authorization\UserAuthorizationChecker;
use App\Domain\User\Enum\UserActivity;
use App\Infrastructure\User\UserUpdaterRepository;
use Psr\Log\LoggerInterface;

final class UserUpdater
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly UserValidator $userValidator,
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
        private readonly UserUpdaterRepository $userUpdaterRepository,
        private readonly UserActivityManager $userActivityManager,
        LoggerFactory $logger
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('user-service');
    }

    /**
     * Update user values.
     * This function is intended for changes coming from a user
     * therefore it changes only "user changeable" general info (not password).
     *
     * @param int $userIdToChange user id on which the change is requested to be made
     * @param array $userValues values to change
     *
     * @return bool
     */
    public function updateUser(int $userIdToChange, array $userValues): bool
    {
        // Working with array and not ClientData object to be able to differentiate values that user wants to set to null
        $this->userValidator->validateUserUpdate($userIdToChange, $userValues);

        // Check if it's admin or if it's its own user
        if ($this->userAuthorizationChecker->isGrantedToUpdate($userValues, $userIdToChange)) {
            // User values to change (cannot use object as unset values would be "null" and remove values in db)
            $validUpdateData = [];
            // Additional check (next to malformed body in action) to be sure that only columns that may be updated are sent to the database
            foreach ($userValues as $column => $value) {
                // Check that keys are one of the database columns that may be updated
                if (in_array($column, [
                    'first_name',
                    'surname',
                    'email',
                    'status',
                    'user_role_id',
                ], true)) {
                    $validUpdateData[$column] = $value;
                } else {
                    throw new NotAllowedException('Not allowed to change client column ' . $column);
                }
            }
            $updated = $this->userUpdaterRepository->updateUser($userIdToChange, $validUpdateData);
            if ($updated) {
                $this->userActivityManager->addUserActivity(
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
        throw new ForbiddenException('Not allowed to change that user');
    }
}
