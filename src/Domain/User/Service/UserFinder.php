<?php


namespace App\Domain\User\Service;


use App\Domain\User\Data\UserData;
use App\Infrastructure\User\UserFinderRepository;

class UserFinder
{
    public function __construct(
        private UserFinderRepository $userFinderRepository
    ) {
    }

    /**
     * @return UserData[]
     */
    public function findAllUsers(): array
    {
        return $this->userFinderRepository->findAllUsers();
    }

    /**
     * @param string $id
     * @param bool $withPasswordHash
     * @return UserData
     */
    public function findUserById(string $id, bool $withPasswordHash = false): UserData
    {
        // Find user in database
        $user = $this->userFinderRepository->findUserById($id);

        // If the password hash is not explicitly needed remove it from object for view and other use cases
        if ($withPasswordHash === false) {
            $user->passwordHash = null;
        }
        return $user;
    }



    /**
     * Find user via email
     *
     * @param string $email
     * @return UserData
     */
    public function findUserByEmail(string $email): UserData
    {
        return $this->userFinderRepository->findUserByEmail($email);
    }
}
