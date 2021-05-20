<?php


namespace App\Domain\Auth\Service;


use App\Infrastructure\User\UserRepository;

class UserRoleFinder
{
    public function __construct(
        private UserRepository $userRepository
    ) { }

    /**
     * Get user role
     *
     * @param int $userId
     * @return string
     */
    public function getUserRoleById(int $userId): string
    {
        return $this->userRepository->getUserRoleById($userId);
    }
}