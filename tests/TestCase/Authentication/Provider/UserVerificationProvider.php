<?php

namespace App\Test\TestCase\Authentication\Provider;

use App\Module\Authentication\Data\UserVerificationData;

class UserVerificationProvider
{
    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function userVerificationProvider(): array
    {
        // Same as in AuthService:createAndSendUserVerification()
        $token = bin2hex(random_bytes(50));

        return [
            [
                'verification' => new UserVerificationData([
                    'id' => 1,
                    'user_id' => 2,
                    'token' => password_hash($token, PASSWORD_DEFAULT),
                    'expires_at' => time() + (60 * 60 * 2), // Time as seconds plus 2h
                    'used_at' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                ]),
                'clearTextToken' => $token,
            ],
        ];
    }

    /**
     * Provides one time invalid and one time expired token.
     *
     * @throws \Exception
     *
     * @return array[]
     */
    public static function userVerificationInvalidTokenProvider(): array
    {
        // Same as in AuthService:createAndSendUserVerification()
        $token = bin2hex(random_bytes(50));

        return [
            // Invalid token
            [
                'verification' => new UserVerificationData([
                    'id' => 1,
                    'user_id' => 2,
                    'token' => password_hash($token, PASSWORD_DEFAULT),
                    'expires_at' => time() + (60 * 60 * 2), // Time as seconds plus 2h
                    'used_at' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                ]),
                'clearTextToken' => 'invalid token', // test relevant
            ],
            // Expired token
            [
                'verification' => new UserVerificationData([
                    'id' => 1,
                    'user_id' => 2,
                    'token' => password_hash($token, PASSWORD_DEFAULT),
                    'expires_at' => time() - 1, // Expired one second ago (test relevant)
                    'used_at' => null,
                    'created_at' => date('Y-m-d H:i:s', time() - 2), // Created 2 seconds ago
                ]),
                'clearTextToken' => $token, // Valid token
            ],
            // Used token
            [
                'verification' => new UserVerificationData([
                    'id' => 1,
                    'user_id' => 2,
                    'token' => password_hash($token, PASSWORD_DEFAULT),
                    'expires_at' => time() + (60 * 60 * 2), // Time as seconds plus 2h
                    'used_at' => date('Y-m-d H:i:s'), // Used
                    'created_at' => date('Y-m-d H:i:s'),
                ]),
                'clearTextToken' => $token, // Valid token
            ],
        ];
    }
}
