<?php

namespace App\Domain\Security;

/**
 * Class ValidationException.
 */
class SecurityException extends \RuntimeException
{

    public const GLOBAL_LOGIN = 'global_login';
    public const GLOBAL_EMAIL = 'global_email';
    public const GLOBAL_REQUESTS = 'global_requests';
    public const USER_LOGIN = 'user_login'; // User or IP fail
    public const USER_EMAIL = 'user_email';
    public const USER_REQUESTS = 'user_requests';

    public function __construct(
        private int|string $remainingDelay,
        private string $type,
        string $message = 'Security check failed.'
    ) {
        parent::__construct($message);
    }

    /**
     * @return int|string int or 'captcha'
     */
    public function getRemainingDelay(): int|string
    {
        return $this->remainingDelay;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
