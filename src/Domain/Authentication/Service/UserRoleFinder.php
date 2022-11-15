<?php


namespace App\Domain\Authentication\Service;


use App\Infrastructure\Authentication\UserRoleFinderRepository;


class UserRoleFinder
{
    public function __construct(
        private UserRoleFinderRepository $userRoleFinderRepository
    ) { }

    /**
     * Get user role
     *
     * @param int $userId
     * @return string
     */
    public function getUserRoleById(int $userId): string
    {
        return $this->userRoleFinderRepository->getUserRoleById($userId);
    }

    public function findAllUserRolesForDropdown(): array
    {
        return $this->userRoleFinderRepository->findAllUserRolesForDropdown();
    }
}