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


}
