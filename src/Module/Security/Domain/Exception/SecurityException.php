<?php

namespace App\Module\Security\Domain\Exception;

use App\Module\Security\Enum\SecurityType;

/**
 * Security throttle exception.
 */
class SecurityException extends \RuntimeException
{
    public function __construct(
        private readonly int|string $remainingDelay,
        private readonly SecurityType $securityType,
        string $message = 'Security check failed.',
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
        $userThrottleMessage = is_numeric($this->remainingDelay) ?
            sprintf(__('wait %s'), '<span class="throttle-time-span">' . $this->remainingDelay . '</span>s')
            : __('fill out the captcha');

        return match ($this->getSecurityType()) {
            SecurityType::USER_LOGIN, SecurityType::USER_EMAIL => sprintf(
                __('It looks like you are doing this too much.<br> Please %s and try again.', $userThrottleMessage)
            ),
            SecurityType::GLOBAL_LOGIN, SecurityType::GLOBAL_EMAIL => __(
                'The site is under a too high request load. 
            <br> Therefore, a general throttling is in place. Please fill out the captcha and try again.'
            ),
            default => 'Please wait or fill out the captcha and repeat the action.',
        };
    }
}
