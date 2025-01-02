<?php

namespace App\Modules\Security\Domain\Service;

use App\Core\Infrastructure\Utility\Settings;
use App\Modules\Security\Domain\Exception\SecurityException;
use App\Modules\Security\Enum\SecurityType;

class SecurityCaptchaVerifier
{
    private array $googleSettings;

    public function __construct(
        Settings $settings,
    ) {
        $this->googleSettings = $settings->get('google');
    }

    /**
     * Ask google API if reCAPTCHA user response is correct or not.
     *
     * @param string $reCaptchaResponse
     * @param SecurityType $exceptionType Exception type (email, login, global)
     *
     * @throws SecurityException
     *
     * @return bool true when correct otherwise SecurityException
     */
    public function verifyReCaptcha(string $reCaptchaResponse, SecurityType $exceptionType): bool
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' .
            urlencode($this->googleSettings['recaptcha']) . '&response=' . urlencode($reCaptchaResponse);
        $verificationResponse = file_get_contents($url);
        if (
            $verificationResponse !== false
            && json_decode($verificationResponse, true, 512, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR)['success']
        ) {
            return true;
        }
        $errMsg = 'reCAPTCHA verification failed';
        throw new SecurityException('captcha', $exceptionType, $errMsg);
    }
}
