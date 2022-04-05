<?php

namespace App\Test\Provider\User;

use App\Domain\User\Data\UserData;
use App\Test\Provider\TestHydrator;

/**
 * Provide users to fill entities
 */
class UserDataProvider
{

    use TestHydrator;

    public array $sampleUsers = [
        [
            'id' => 1,
            'first_name' => 'Admin',
            'surname' => 'Example',
            'email' => 'admin@example.com',
            'password' => '12345678',
            'password2' => '12345678',
            'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
            'role' => 'admin'
        ],
        [
            'id' => 2,
            'first_name' => 'Steve',
            'surname' => 'Jobs',
            'email' => 'jobs@email.com',
            'password' => '12345678',
            'password2' => '12345678',
            'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
            'role' => 'user'
        ],
        [
            'id' => 3,
            'first_name' => 'Mark',
            'surname' => 'Zuckerberg',
            'email' => 'zuckerberg@email.com',
            'password' => '12345678',
            'password2' => '12345678',
            'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
            'role' => 'user'
        ],
        [
            'id' => 4,
            'first_name' => 'Evan',
            'surname' => 'Spiegel',
            'email' => 'spiegel@email.com',
            'password' => '12345678',
            'password2' => '12345678',
            'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
            'role' => 'user'
        ],
        [
            'id' => 5,
            'first_name' => 'Jack',
            'surname' => 'Dorsey',
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
     * @return UserData[]
     */
    public function oneSetOfMultipleUserObjectsProvider(): array
    {
        return [
            [
                $this->hydrate($this->sampleUsers, UserData::class)
            ]
        ];
    }

    /**
     * Hydrate User objects.
     * Placed in each provider as I'm unsure about
     * @param array $rows
     * @return UserData[]
     */
    public function hydrateUsers(array $rows): array
    {
        /** @var UserData[] $result */
        $result = [];

        foreach ($rows as $row) {
            $result[] = new UserData($row);
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
                    'first_name' => 'Bill',
                    'surname' => 'Gates',
                    'email' => 'gates@email.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin',
                    'status' => UserData::STATUS_ACTIVE,
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
                new UserData([
                    'id' => 1,
                    'first_name' => 'Bill',
                    'surname' => 'Gates',
                    'email' => 'gates@email.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin',
                    'status' => UserData::STATUS_ACTIVE,
                ])
            ]
        ];
    }

    /**
     * Provide one user in a DataProvider format
     *
     * @return array
     */
    public function oneUserObjectAndClientDataProvider(): array
    {
        return [
            [
                // User values from client form submit
                'userData' => [
                    'first_name' => 'Bill',
                    'surname' => 'Gates',
                    'email' => 'gates@email.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                ],
                // User object from repository
                'userObj' => new UserData([
                    'id' => 1,
                    'first_name' => 'Bill',
                    'surname' => 'Gates',
                    'email' => 'gates@email.com',
                    'password_hash' => password_hash('12345678', PASSWORD_DEFAULT),
                    'role' => 'admin',
                    'status' => UserData::STATUS_ACTIVE,
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
                    'first_name' => 'B',
                    'surname' => 'G',
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
                    'first_name' => 'Bill',
                    'surname' => 'Gates',
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
                    'first_name' => 'Bill',
                    'surname' => 'Gates',
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
                    'first_name' => 'B',
                    'surname' => 'G',
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
                    'first_name' => 'B',
                    'surname' => 'G',
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
                    'first_name' => 'Bill',
                    'surname' => 'Gates',
                    'email' => 'gates@ema$il.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                ]
            ],
            // Email not set
            [
                [
                    'id' => 1,
                    'first_name' => 'Bill',
                    'surname' => 'Gates',
                    'email' => '',
                    'password' => '',
                    'password2' => '12345678',
                ]
            ],
            // Password not set
            [
                [
                    'id' => 1,
                    'first_name' => 'Bill',
                    'surname' => 'Gates',
                    'email' => 'gates@email.com',
                    'password' => '',
                    'password2' => '12345678',
                ]
            ],
            // Password too short
            [
                [
                    'id' => 1,
                    'first_name' => 'Bill',
                    'surname' => 'Gates',
                    'email' => 'gates@email.com',
                    'password' => '12',
                    'password2' => '12345678',
                ]
            ],
        ];
    }


    // For integration tests

    /**
     * Providing arrays of how malformed request body can look like
     * Error messages have to be identical to RegisterSubmitAction.php
     * asserting error message to differentiate empty body and malformed body.
     * Used to test register and update.
     *
     * @return array[][]
     */
    public function malformedRequestBodyProvider(): array
    {
        return [
            [
                // Empty body
                'body' => [],
                'message' => 'Request body is empty.',
            ],
            [
                // Body "null" (because both can happen )
                'body' => null,
                'message' => 'Request body is empty.',
            ],
            [
                // 5th parameter and client trying to set role
                'body' => [
                    // Same keys than HTML form
                    'first_name' => '',
                    'surname' => '',
                    'email' => '',
                    'password' => '',
                    'password2' => '',
                    'user_role' => 'admin',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'first_name'
                'body' => [
                    'surname' => '',
                    'email' => '',
                    'password' => '',
                    'password2' => '',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'surname'
                'body' => [
                    'first_name' => '',
                    'email' => '',
                    'password' => '',
                    'password2' => '',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'email'
                'body' => [
                    'first_name' => '',
                    'surname' => '',
                    'password' => '',
                    'password2' => '',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'password'
                'body' => [
                    'first_name' => '',
                    'surname' => '',
                    'email' => '',
                    'password2' => '',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'password2'
                'body' => [
                    'first_name' => '',
                    'surname' => '',
                    'email' => '',
                    'password' => '',
                ],
                'message' => 'Request body malformed.',
            ],
        ];
    }

    /**
     * Provide malformed bodies for password change submit request as well as
     * according error messages
     * @return array[]
     */
    public function malformedPasswordChangeRequestBodyProvider(): array
    {
        return [
            [
                // Empty body
                'body' => [],
                'message' => 'Password change request malformed.',
            ],
            [
                // Body "null" (because both can happen )
                'body' => null,
                'message' => 'Password change request malformed.',
            ],
            [
                // Leaving out 'old_password'
                'body' => [
                    'password' => '',
                    'password2' => '',
                ],
                'message' => 'Password change request malformed.',
            ],
            [
                // Leaving out 'password'
                'body' => [
                    'old_password' => '',
                    'password2' => '',
                ],
                'message' => 'Password change request malformed.',
            ],
            [
                // Leaving out 'password2'
                'body' => [
                    'old_password' => '',
                    'password' => '',
                ],
                'message' => 'Password change request malformed.',
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
     * Correct login credentials provider
     *
     * @return string[][][]
     */
    public function correctLoginCredentialsProvider(): array
    {
        return [
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