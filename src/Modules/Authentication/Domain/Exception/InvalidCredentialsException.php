<?php

namespace App\Modules\Authentication\Domain\Exception;

class InvalidCredentialsException extends AuthenticationException
{
    // Voluntarily not more information
    private string $userEmail;

    // Invalid credentials asserted in LoginSubmitActionTest
    public function __construct(string $email, string $message = 'Invalid credentials')
    {
        parent::__construct($message);
        $this->userEmail = $email;
    }

    /**
     * @return string
     */
    public function getUserEmail(): string
    {
        return $this->userEmail;
    }
}
