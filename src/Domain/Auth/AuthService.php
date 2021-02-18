<?php


namespace App\Domain\Auth;

use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Settings;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use App\Infrastructure\User\UserRepository;

/**
 * Authentication logic
 * Class AuthService
 * @package App\Domain\Auth
 */
class AuthService
{
    private UserValidation $userValidation;
    private UserService $userService;
    private UserRepository $userRepository;

    public function __construct(
        UserValidation $userValidation,
        UserRepository $userRepository,
        UserService $userService
    ) {
        $this->userValidation = $userValidation;
        $this->userService = $userService;
        $this->userRepository = $userRepository;
    }

    /**
     * Checks if user is allowed to login.
     * If yes, the user object is returned with id
     * If no, an InvalidCredentialsException is thrown
     *
     * @param User $user
     * @return string id
     *
     * @throws InvalidCredentialsException
     *
     *
     */
    public function getUserIdIfAllowedToLogin(User $user): string
    {
        $this->userValidation->validateUserLogin($user);

        $dbUser = $this->userService->findUserByEmail($user->getEmail());
        if ($dbUser !== null && $dbUser !== [] && password_verify($user->getPassword(), $dbUser['password_hash'])) {
            return $dbUser['id'];
        }

        // Throw InvalidCred exception if user doesn't exist or wrong password
        // (vague exception on purpose for security)
        throw new InvalidCredentialsException($user->getEmail());
    }

    /**
     * Get user role
     *
     * @param int $id
     * @return string
     */
    public function getUserRole(int $id): string
    {
        return $this->userRepository->getUserRole($id);
    }
}