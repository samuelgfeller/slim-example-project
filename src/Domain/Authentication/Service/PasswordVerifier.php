<?php


namespace App\Domain\Authentication\Service;


use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\UnauthorizedException;
use App\Infrastructure\User\UserFinderRepository;
use Odan\Session\SessionInterface;


class PasswordVerifier
{

    public function __construct(
        private UserFinderRepository $userFinderRepository,
        private SessionInterface $session,
    ) {
    }

    /**
     * Checks if given password is correct for the user
     * @param string $oldPassword
     * @return bool
     * @throws InvalidCredentialsException|UnauthorizedException
     */
    public function verifyPassword(string $oldPassword): bool
    {
        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $dbUser = $this->userFinderRepository->findUserById((int)$loggedInUserId);
            if (password_verify($oldPassword, $dbUser->passwordHash)) {
                return true;
            }
            throw new InvalidCredentialsException(
                $dbUser->email, 'Provided invalid password on password change request'
            );
        }
        throw new UnauthorizedException(
            'User trying to change password without being authenticated. This 
        exception should not be able to exist, something is wrong with the UserAuthenticationMiddleware.'
        );
    }
}