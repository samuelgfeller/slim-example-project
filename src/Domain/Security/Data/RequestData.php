<?php

namespace App\Domain\Security\Data;

class RequestData
{
    public ?int $id;
    public ?string $email;
    public ?string $ipAddress;
    public ?bool $sentEmail;
    public ?string $isLogin;
    public ?\DateTimeImmutable $createdAt;

    /**
     * @param array $requestData
     *
     * @throws \Exception
     */
    public function __construct(array $requestData = [])
    {
        $this->id = $requestData['id'] ?? null;
        $this->email = $requestData['email'] ?? null;
        $this->ipAddress = $requestData['ip_address'] ?? null;
        $this->sentEmail = $requestData['sent_email'] ?? null;
        $this->isLogin = $requestData['is_login'] ?? null;
        $this->createdAt = $requestData['created_at'] ?? null
            ? new \DateTimeImmutable($requestData['created_at']) : null;
    }
}
