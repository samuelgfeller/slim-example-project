<?php


namespace App\Domain\User;

class UserService {
    
    private $userRepositoryInterface;
    
    public function __construct(UserRepositoryInterface $userRepositoryInterface) { 
        $this->userRepositoryInterface = $userRepositoryInterface;
    }

    public function findAllUsers() {
        $allUsers= $this->userRepositoryInterface->findAllUsers();
        return $allUsers;
    }

    public function findUser($id): array
    {
        return $this->userRepositoryInterface->findUserById($id);
    }

    public function findUserByEmail($email): array
    {
        return $this->userRepositoryInterface->findUserByEmail($email);
    }

    /**
     * Insert user in database
     *
     * @param $data
     * @return string
     */
    public function createUser($data): string
    {
        return $this->userRepositoryInterface->insertUser($data);
    }

    public function updateUser($id,$name,$email): bool
    {
        $data = [
            'name' => $name,
            'email' => $email
        ];
        return $this->userRepositoryInterface->updateuser($data,$id);
    }

    public function deleteUser($id): bool
    {
        return $this->userRepositoryInterface->deleteUser($id);
    }

    /**
     * Get user role
     *
     * @param $id
     * @return string
     * @throws \App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException
     */
    public function getUserRole(int $id): string
    {
        return $this->userRepositoryInterface->getUserRole($id);
    }


}
