<?php


namespace App\Domain\User\Service;


use App\Domain\User\DTO\User;
use App\Infrastructure\User\UserRepository;

class UserFinder
{
    public function __construct(
        private UserRepository $userRepository
    ) { }

    /**
     * @return User[]
     */
    public function findAllUsers(): array
    {
        return $this->userRepository->findAllUsers();
    }

    /**
     * @param string $id
     * @return User
     */
    public function findUserById(string $id): User
    {
        return $this->userRepository->findUserById($id);
    }

    /**
     * Find user via email
     *
     * @param string $email
     * @return User
     */
    public function findUserByEmail(string $email): User
    {
        return $this->userRepository->findUserByEmail($email);
    }
}