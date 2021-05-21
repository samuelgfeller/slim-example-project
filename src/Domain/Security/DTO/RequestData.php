<?php


namespace App\Domain\Security\DTO;


use App\Common\ArrayReader;

class RequestData
{
    public ?int $id;
    public ?string $email;
    public ?string $ipAddress;
    public ?bool $sentEmail;
    public ?string $isLogin;
    public ?string $createdAt;

    public function __construct(array $data = []) {
        $reader = new ArrayReader($data);
        $this->id = $reader->findInt('id');
        $this->email = $reader->findString('email');
        $this->ipAddress = $reader->findString('ip_address');
        $this->sentEmail = $reader->findBool('sent_email');
        $this->isLogin = $reader->findString('is_login');
        $this->createdAt = $reader->findString('created_at');
    }
}