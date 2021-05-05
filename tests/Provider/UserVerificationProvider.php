<?php

namespace App\Test\Provider;

use App\Domain\Auth\DTO\UserVerification;

class UserVerificationProvider
{

    /**
     * @return array[]
     */
    public function verifyUserProvider(): array
    {
        // Same as in AuthService:createAndSendUserVerification()
        $token = random_bytes(50);
        return [
            [
                'verification' => new UserVerification([
                    'id' => 1,
                    'user_id' => 1,
                    'token' => password_hash($token, PASSWORD_DEFAULT),
                    'expires' => time() + (60 * 60 * 2), // Time as seconds plus 2h
                    'used_at' => null,
                    'created_at' => date('Y-m-d H:i:s')
                ]),
                'token' => $token
            ],
        ];
    }

    /**
     * Provides one time invalid and one time expired token
     *
     * @return array[]
     * @throws \Exception
     */
    public function invalidExpiredToken(): array
    {
        // Same as in AuthService:createAndSendUserVerification()
        $token = random_bytes(50);
        return [
            // Invalid token
            [
                'verification' => new UserVerification([
                    'id' => 1,
                    'user_id' => 1,
                    'token' => password_hash($token, PASSWORD_DEFAULT),
                    'expires' => time() + (60 * 60 * 2), // Time as seconds plus 2h
                    'used_at' => null,
                    'created_at' => date('Y-m-d H:i:s')
                ]),
                'token' => 'invalid token' // test relevant
            ],
            // Expired token
            [
                'verification' => new UserVerification([
                    'id' => 1,
                    'user_id' => 1,
                    'token' => password_hash($token, PASSWORD_DEFAULT),
                    'expires' => time() - 1, // Expired one second ago (test relevant)
                    'used_at' => null,
                    'created_at' => date('Y-m-d H:i:s', time() - 2), // Created 2 seconds ago
                ]),
                'token' => $token, // Valid token
            ],
        ];
    }



}