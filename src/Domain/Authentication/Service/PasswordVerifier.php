<?php


namespace App\Domain\Authentication\Service;


use App\Domain\Exceptions\InvalidCredentialsException;
use App\Infrastructure\User\UserFinderRepository;


class PasswordVerifier
{

    public function __construct(
        private readonly UserFinderRepository $userFinderRepository,
    ) {
    }

    /**
     * Checks if given password is correct for the user
     * @param string $oldPassword
     * @param int $userId
     * @return bool
     */
    public function verifyPassword(string $oldPassword, int $userId): bool
    {
        $dbUser = $this->userFinderRepository->findUserByIdWithPasswordHash($userId);
        if (password_verify($oldPassword, $dbUser->passwordHash)) {
            return true;
        }
        throw new InvalidCredentialsException(
            $dbUser->email, 'Incorrect old password.'
        );
    }
}