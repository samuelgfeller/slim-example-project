<?php


namespace App\Domain\User;

use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Settings;
use App\Infrastructure\Persistence\User\UserRepository;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;

class UserService
{
    
    private UserRepository $userRepository;
    protected UserValidation $userValidation;
    protected LoggerInterface $logger;
    private array $jwtSettings;

    
    public function __construct(UserRepository $userRepository, UserValidation $userValidation,LoggerInterface $logger, Settings $settings)
    {
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
        $this->logger = $logger;
        $this->jwtSettings = $settings->get('jwt');
    }
    
    public function findAllUsers()
    {
        $allUsers = $this->userRepository->findAllUsers();
        return $allUsers;
    }
    
    public function findUser(int $id): array
    {
        return $this->userRepository->findUserById($id);
    }

    /**
     * @param string $email
     * @return array|null
     */
    public function findUserByEmail(string $email):? array
    {
        return $this->userRepository->findUserByEmail($email);
    }
    
    /**
     * Insert user in database
     *
     * @param $user
     * @return string
     */
    public function createUser(User $user): string
    {
        $this->userValidation->validateUserRegistration($user);
        $user->setPassword(password_hash($user->getPassword(), PASSWORD_DEFAULT));
        return $this->userRepository->insertUser($user->toArray());
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

        $dbUser = $this->findUserByEmail($user->getEmail());
        //$this->logger->info('users/' . $user . ' has been called');
        if($dbUser !== null && $dbUser !== [] && password_verify($user->getPassword(), $dbUser['password'])){
            $user->setId($dbUser['id']);
            return $user;
        }

        // Throw exception if user is not returned to controller
        throw new InvalidCredentialsException($user->getEmail());
    }

    /**
     * @param User $user id MUST be in object
     * @return bool
     */
    public function updateUser(User $user): bool
    {

        $this->userValidation->validateUserUpdate($user);

        $userData = [];
        if ($user->getName()!== null) {
            $userData['name'] = $user->getName();
        }
        if ($user->getEmail() !== null) {
            $userData['email'] = $user->getEmail();
        }
        if ($user->getPassword() !== null) {
            // passwords are already identical since they were validated in UserValidation.php
            $userData['password'] = password_hash($user->getPassword(), PASSWORD_DEFAULT);
        }

        return $this->userRepository->updateuser($userData, $user->getId());
    }

    public function deleteUser($id): bool
    {
        // todo delete posts
        return $this->userRepository->deleteUser($id);
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
