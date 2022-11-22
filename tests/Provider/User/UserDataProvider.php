<?php

namespace App\Test\Provider\User;

/**
 * Provide users to fill entities
 */
class UserDataProvider
{

    /**
     * Provide malformed request bodies for password reset submit request as well as
     * according error messages
     * @return array[]
     */
    public function malformedPasswordResetRequestBodyProvider(): array
    {
        return [
            [
                // Empty body
                'body' => [],
                'message' => 'Request body malformed.',
            ],
            [
                // Body "null" (because both can happen )
                'body' => null,
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'password'
                'body' => [
                    'password2' => '',
                    'token' => '',
                    'id' => '',
                ],
                'message' => 'Request body malformed.',
            ],
                        [
                // Leaving out 'password2'
                'body' => [
                    'password' => '',
                    'token' => '',
                    'id' => '',
                ],
                'message' => 'Request body malformed.',
            ],
                        [
                // Leaving out 'token'
                'body' => [
                    'password' => '',
                    'password2' => '',
                    'id' => '',
                ],
                'message' => 'Request body malformed.',
            ],
                        [
                // Leaving out 'id'
                'body' => [
                    'password' => '',
                    'password2' => '',
                    'token' => '',
                ],
                'message' => 'Request body malformed.',
            ],
        ];
    }

    /**
     * Provides one time valid login credentials matching the password
     * of tests/Fixture/UserFixture.php and one time not
     *
     * @return array[]
     */
    public function correctAndWrongCredentialsProvider(): array
    {
        return [
            // Invalid password
            [
                [
                    // Same keys than HTML form
                    'email' => 'admin@example.com',
                    'password' => 'abcdefg',
                ],
            ],
            // Correct credentials (inserted with tests/Fixture/UserFixture.php)
            [
                [
                    // Same keys than HTML form
                    'email' => 'admin@example.com',
                    'password' => '12345678',
                ],
            ]
        ];
    }

    /**
     * Correct login credentials provider of user with role user
     *
     * @return string[][][]
     */
    public function userLoginCredentialsProvider(): array
    {
        return [
            // Correct credentials (inserted with tests/Fixture/UserFixture.php)
            [
                [
                    // Same keys than HTML form
                    'email' => 'user@example.com',
                    'password' => '12345678',
                ],

            ]
        ];
    }

    // Wrong not needed as anything can be taken

    /**
     * Invalid login credentials provider that should fail validation
     *
     * @return string[][][]
     */
    public function invalidLoginCredentialsProvider(): array
    {
        return [
            [
                [
                    // Invalid email
                    'email' => 'admin@exam$ple.com',
                    'password' => '12345678',
                ],
            ],
            [
                [
                    // Missing email
                    'email' => '',
                    'password' => '12345678',
                ],
            ],
            [
                [
                    // Invalid password
                    'email' => 'admin@example.com',
                    'password' => '12',
                ],
            ],
            [
                [
                    // Missing password
                    'email' => 'admin@example.com',
                    'password' => '',
                ],
            ]
        ];
    }
}