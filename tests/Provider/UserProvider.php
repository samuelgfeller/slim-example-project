<?php

namespace App\Test\Provider;

use App\Domain\User\User;

/**
 * Provide users to fill entities
 */
class UserProvider
{

    use TestHydrator;

    public array $sampleUsers = [
        [
            'id' => 1,
            'name' => 'Admin Example',
            'email' => 'admin@example.com',
            'password' => '12345678',
            'password2' => '12345678',
            'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
            'role' => 'admin'
        ],
        [
            'id' => 2,
            'name' => 'Steve Jobs',
            'email' => 'jobs@email.com',
            'password' => '12345678',
            'password2' => '12345678',
            'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
            'role' => 'user'
        ],
        [
            'id' => 3,
            'name' => 'Mark Zuckerberg',
            'email' => 'zuckerberg@email.com',
            'password' => '12345678',
            'password2' => '12345678',
            'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
            'role' => 'user'
        ],
        [
            'id' => 4,
            'name' => 'Evan Spiegel',
            'email' => 'spiegel@email.com',
            'password' => '12345678',
            'password2' => '12345678',
            'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
            'role' => 'user'
        ],
        [
            'id' => 5,
            'name' => 'Jack Dorsey',
            'email' => 'dorsey@email.com',
            'password' => '12345678',
            'password2' => '12345678',
            'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
            'role' => 'user'
        ],
    ];


    public function validUserProvider(): array
    {
        return [
            $this->sampleUsers,
        ];
    }

    /**
     * Provide a set of users in a DataProvider format
     *
     * @return array of users
     */
    public function oneSetOfMultipleUsersProvider(): array
    {
        return [
            [$this->sampleUsers]
        ];
    }

    /**
     * Provide a set of user objects
     *
     * @return User[]
     */
    public function oneSetOfMultipleUserObjectsProvider(): array
    {
        return [
            [
                $this->hydrate($this->sampleUsers, User::class)
            ]
        ];
    }

    /**
     * Hydrate User objects.
     * Placed in each provider as I'm unsure about
     * @param array $rows
     * @return User[]
     */
    public function hydrateUsers(array $rows): array
    {
        /** @var User[] $result */
        $result = [];

        foreach ($rows as $row) {
            $result[] = new User($row);
        }

        return $result;
    }

    /**
     * Provide one user in a DataProvider format
     *
     * @return array
     */
    public function oneUserProvider(): array
    {
        return [
            [
                [
                    'id' => 1,
                    'name' => 'Bill Gates',
                    'email' => 'gates@email.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin',
                    'status' => User::STATUS_ACTIVE,
                ]
            ]
        ];
    }

    /**
     * Provide one user in a DataProvider format
     *
     * @return array
     */
    public function oneUserObjectProvider(): array
    {
        return [
            [
                new User([
                    'id' => 1,
                    'name' => 'Bill Gates',
                    'email' => 'gates@email.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin',
                    'status' => User::STATUS_ACTIVE,
                ])
            ]
        ];
    }

    /**
     * @return array
     */
    public function invalidUserProvider(): array
    {
        return [
            // Not existing user not unit tested
            // Name too short
            [
                [
                    'id' => 2,
                    'name' => 'B',
                    'email' => 'gates@email.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin'
                ]
            ],
            // Invalid Email
            [
                [
                    'id' => 1,
                    'name' => 'Bill Gates',
                    'email' => 'gates@ema$il.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin'
                ]
            ],
            // Not matching Passwords
            [
                [
                    'id' => 1,
                    'name' => 'Bill Gates',
                    'email' => 'gates@email.com',
                    'password' => '123456789',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin'
                ]
            ],
            // Required values not set not in this provider as for updateUser nothing is required
        ];
        // Could add more rows with always 1 required missing because now error could be thrown
        // by another missing field.
    }

    public function invalidUserForUpdate(): array
    {
        return [
            // Name too short
            [
                [
                    'id' => 1,
                    'name' => 'B',
                    'email' => 'gates@email.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin'
                ]
            ],
            // Invalid Email
            [
                [
                    'id' => 2,
                    'name' => 'B',
                    'email' => 'gates@e$mail.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin'
                ]
            ],
            ];
    }

    /**
     * @return array
     */
    public function invalidEmailAndPasswordsUsersProvider(): array
    {
        return [
            // Invalid Email
            [
                [
                    'id' => 1,
                    'name' => 'Bill Gates',
                    'email' => 'gates@ema$il.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin'
                ]
            ],
            // Email not set
            [
                [
                    'id' => 1,
                    'name' => 'Bill Gates',
                    'email' => '',
                    'password' => '',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin'
                ]
            ],
            // Password not set
            [
                [
                    'id' => 1,
                    'name' => 'Bill Gates',
                    'email' => 'gates@email.com',
                    'password' => '',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin'
                ]
            ],
        ];
    }


    // For integration tests

    /**
     * Providing arrays of how malformed request body can look like
     * Error messages have to be identical to RegisterSubmitAction.php
     * asserting error message to differentiate empty body and malformed body
     *
     * @return array[][]
     */
    public function malformedRequestBodyProvider(): array
    {
        return [
            [
                // Empty body
                'body' => [],
                'message' => 'Request body is empty',
            ],
            [
                // Body "null" (because both can happen )
                'body' => null,
                'message' => 'Request body is empty',
            ],
            [
                // 5th parameter and client trying to set role
                'body' => [
                    // Same keys than HTML form
                    'name' => '',
                    'email' => '',
                    'password' => '',
                    'password2' => '',
                    'user_role' => 'admin',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'name'
                'body' => [
                    'email' => '',
                    'password' => '',
                    'password2' => '',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'email'
                'body' => [
                    'name' => '',
                    'password' => '',
                    'password2' => '',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'password'
                'body' => [
                    'name' => '',
                    'email' => '',
                    'password2' => '',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'password2'
                'body' => [
                    'name' => '',
                    'email' => '',
                    'password' => '',
                ],
                'message' => 'Request body malformed.',
            ],
        ];
    }

    /**
     * Provides one time valid user login values matching the password
     * of tests/Fixture/UserFixture.php and one time not
     *
     * @return array[]
     */
    public function loginUserProvider(): array
    {
        return [
            // Invalid
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

}