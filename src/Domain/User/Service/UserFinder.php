<?php


namespace App\Domain\User\Service;


use App\Domain\User\Data\UserData;
use App\Infrastructure\User\UserFinderRepository;

class UserFinder
{
    public function __construct(
        private UserFinderRepository $userFinderRepository
    ) { }

    /**
     * @return UserData[]
     */
    public function findAllUsers(): array
    {
        return $this->userFinderRepository->findAllUsers();
    }

    /**
     * @param string $id
     * @return UserData
     */
    public function findUserById(string $id): UserData
    {
        return $this->userFinderRepository->findUserById($id);
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