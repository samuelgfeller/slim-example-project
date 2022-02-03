<?php


namespace App\Domain\User\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Data\UserData;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\User\UserUpdaterRepository;
use Psr\Log\LoggerInterface;

final class UserUpdater
{
    private LoggerInterface $logger;

    public function __construct(
        private UserValidator $userValidator,
        private UserRoleFinderRepository $userRoleFinderRepository,
        private UserUpdaterRepository $userUpdaterRepository,
        LoggerFactory $logger
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('user-service');
    }

    /**
     * Update user values.
     * This function is intended for changes coming from a user
     * therefore it changes only "user changeable" general info (not password)
     *
     * @param int $userIdToChange user id on which the change is requested to be made
     * @param array $userValues values to change
     * @param int $loggedInUserId
     * @return bool
     */
    public function updateUser(int $userIdToChange, array $userValues, int $loggedInUserId): bool
    {
        $user = new UserData($userValues, true);
        $this->userValidator->validateUserUpdate($userIdToChange, $user);

        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);
        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $userIdToChange === $loggedInUserId) {
            // User values to change (cannot use object as unset values would be "null" and remove values in db)
            $validUpdateValues = [];
            // Data to be changed is set here. It can easily be controlled which data this function is allowed to change here
            // instead of removing the fields that are not allowed to be edited with this function (password, role etc.)
            if ($user->name !== null) {
                $validUpdateValues['name'] = $user->name;
            }
            if ($user->email !== null) {
                $validUpdateValues['email'] = $user->email;
            }
            return $this->userUpdaterRepository->updateUser($userIdToChange, $validUpdateValues);
        }

        // User does not have needed rights to access area or function
        $this->logger->notice(
            'User ' . $loggedInUserId . ' tried to update other user with id: ' . $userIdToChange
        );
        throw new ForbiddenException('Not allowed to change that user');
    }
}