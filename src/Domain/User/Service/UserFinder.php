<?php


namespace App\Domain\User\Service;


use App\Domain\User\DTO\User;
use App\Infrastructure\User\UserFinderRepository;

class UserFinder
{
    public function __construct(
        private UserFinderRepository $userFinderRepository
    ) { }

    /**
     * @return User[]
     */
    public function findAllUsers(): array
    {
        return $this->userFinderRepository->findAllUsers();
    }

    /**
     * @param string $id
     * @return User
     */
    public function findUserById(string $id): User
    {
        return $this->userFinderRepository->findUserById($id);
    }

    /**
     * Find user via email
     *
     * @param string $email
     * @return User
     */
    public function findUserByEmail(string $email): User
    {
        return $this->userFinderRepository->findUserByEmail($email);
    }
}