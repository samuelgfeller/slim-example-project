<?php

namespace App\Module\Security\Captcha\Service;

use App\Infrastructure\Settings\Settings;
use App\Module\Security\Enum\SecurityType;
use App\Module\Security\Exception\SecurityException;

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
