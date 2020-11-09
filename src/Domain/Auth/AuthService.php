<?php


namespace App\Domain\Auth;

use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Settings;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use App\Infrastructure\User\UserRepository;
use Firebase\JWT\JWT;

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
    private $jwtSettings;

    public function __construct(UserValidation $userValidation, UserRepository $userRepository, UserService $userService, Settings $settings)
    {
        $this->userValidation = $userValidation;
        $this->userService = $userService;
        $this->userRepository = $userRepository;
        $this->jwtSettings = $settings->get(JWT::class);
    }

    /**
     * Checks if user is allowed to login.
     * If yes, the user object is returned with id
     * If no, an InvalidCredentialsException is thrown
     *
     * @param User $user
     * @return User $user
     *
     * @throws InvalidCredentialsException
     *
     */
    public function getUserWithIdIfAllowedToLogin(User $user): User
    {
        $this->userValidation->validateUserLogin($user);

        $dbUser = $this->userService->findUserByEmail($user->getEmail());
        if($dbUser !== null && $dbUser !== [] && password_verify($user->getPassword(), $dbUser['password'])){
            $user->setId($dbUser['id']);
            // Maybe would make sense to return hydrated obj with db values for the case that login data ->
            // don't provide all infos for user obj
            return $user;
        }

        // Throw InvalidCred exception if user doesn't exist or wrong password
        // (vague exception on purpose for security)
        throw new InvalidCredentialsException($user->getEmail());
    }

    /**
     * Generates a JWT Token with user id
     * todo move to jwt service
     *
     * @param User $user
     * @return string
     */
    public function generateToken(User $user)
    {
        $durationInSec = 5000; // In seconds
        $tokenId = base64_encode(random_bytes(32));
        $issuedAt = time();
        $notBefore = $issuedAt + 2;             //Adding 2 seconds
        $expire = $notBefore + $durationInSec;            // Adding 300 seconds

        $data = [
            'iat' => $issuedAt,         // Issued at: time when the token was generated
            'jti' => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss' => 'MyApp',       // Issuer
            'nbf' => $notBefore,        // Not before
            'exp' => $expire,           // Expire
            'data' => [                  // Data related to the signer user
                'userId' => $user->getId(), // userid from the users table
            ]
        ];

        return JWT::encode($data, $this->jwtSettings['secret'], $this->jwtSettings['algorithm']);


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