<?php

namespace App\Domain\Security\Exception;

use App\Domain\Security\Enum\SecurityType;

/**
 * Security throttle exception.
 */
class SecurityException extends \RuntimeException
{
    public function __construct(
        private readonly int|string $remainingDelay,
        private readonly SecurityType $securityType,
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

    public function getSecurityType(): SecurityType
    {
        return $this->securityType;
    }

    public function getPublicMessage(): string
    {
        return match ($this->getSecurityType()) {
            SecurityType::USER_LOGIN, SecurityType::USER_EMAIL => 'It looks like you are doing this too much. <br> Please ' .
                (is_numeric(
                    $this->remainingDelay
                ) ? 'wait <span class="throttle-time-span">' . $this->remainingDelay . '</span>s'
                    : 'fill out the captcha') .
                ' and try again.',
            SecurityType::GLOBAL_LOGIN, SecurityType::GLOBAL_EMAIL => 'The site is under a too high request load ' .
                'therefore a general throttling is in place. Please fill out the captcha and try again.',
            default => 'Please wait or fill out the captcha and repeat the action.',
        };
    }
}
