<?php


namespace App\Domain\User;

use App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;

class UserService
{
    
    private UserRepositoryInterface $userRepositoryInterface;
    protected UserValidation $userValidation;
    protected LoggerInterface $logger;

    
    public function __construct(UserRepositoryInterface $userRepositoryInterface, UserValidation $userValidation,LoggerInterface $logger)
    {
        $this->userRepositoryInterface = $userRepositoryInterface;
        $this->userValidation = $userValidation;
        $this->logger = $logger;
    }
    
    public function findAllUsers()
    {
        $allUsers = $this->userRepositoryInterface->findAllUsers();
        return $allUsers;
    }
    
    public function findUser(int $id): array
    {
        return $this->userRepositoryInterface->findUserById($id);
    }

    /**
     * @param string $email
     * @return array|null
     */
    public function findUserByEmail(string $email):? array
    {
        return $this->userRepositoryInterface->findUserByEmail($email);
    }
    
    /**
     * Insert user in database
     *
     * @param $user
     * @return string
     */
    public function createUser(User $user): string
    {
        // todo validate with object
//        $this->userValidation->validateUserRegistration($data);
        return $this->userRepositoryInterface->insertUser($user->toArray());
    }

    /**
     * Checks if user is allowed to login.
     * If yes, the user object is returned with id
     * If no, null is returned
     *
     * @param User $user
     * @return mixed|null
     */
    public function userAllowedToLogin(User $user)
    {
        // todo do validation
//        $validationResult = $this->userValidation->validateUserLogin($parsedBody);

        $dbUser = $this->findUserByEmail($user->getEmail());
        //$this->logger->info('users/' . $user . ' has been called');
        if($dbUser !== null && $dbUser !== [] && password_verify($user->getPassword(), $dbUser['password'])){
            $user->setId($dbUser['id']);
            return $user;
        }
        return null;
    }

    /**
     * @param $id
     * @param $data array Data to update
     * @return bool
     */
    public function updateUser($id, array $data): bool
    {
        $validatedData = [];
        if (isset($data['name'])) {
            $validatedData['name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $validatedData['email'] = $data['email'];
        }
        if (isset($data['password1'], $data['password2'])) {
            // passwords are already identical since they were validated in UserValidation.php
            $validatedData['password'] = password_hash($data['password1'], PASSWORD_DEFAULT);
        }
        
        
        return $this->userRepositoryInterface->updateuser($validatedData, $id);
    }
    
    public function deleteUser($id): bool
    {
        // todo delete posts
        return $this->userRepositoryInterface->deleteUser($id);
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
        $durationInSec = 500; // In seconds
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

        return JWT::encode($data, 'test', 'HS256'); // todo change test to settings


    }
    
    /**
     * Get user role
     *
     * @param int $id
     * @return string
     */
    public function getUserRole(int $id): string
    {
        return $this->userRepositoryInterface->getUserRole($id);
    }
    
}
