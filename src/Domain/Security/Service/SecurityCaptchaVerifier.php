<?php

namespace App\Domain\Security\Service;

use App\Domain\Security\Enum\SecurityType;
use App\Domain\Security\Exception\SecurityException;
use App\Domain\Settings;

class SecurityCaptchaVerifier
{
    private array $googleSettings;

    public function __construct(
        Settings $settings
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
            $verificationResponse !== false &&
            json_decode($verificationResponse, true, 512, JSON_THROW_ON_ERROR)['success']
        ) {
            return true;
        }
        $errMsg = 'reCAPTCHA verification failed';
        throw new SecurityException('captcha', $exceptionType, $errMsg);
    }
}
