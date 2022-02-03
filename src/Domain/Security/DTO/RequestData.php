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
        $this->id = $reader->findAsInt('id');
        $this->email = $reader->findAsString('email');
        $this->ipAddress = $reader->findAsString('ip_address');
        $this->sentEmail = $reader->findAsBool('sent_email');
        $this->isLogin = $reader->findAsString('is_login');
        $this->createdAt = $reader->findAsString('created_at');
    }
}