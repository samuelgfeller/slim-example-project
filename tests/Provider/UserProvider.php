<?php

namespace App\Test\Provider;

use App\Domain\User\User;
use App\Domain\Utility\ArrayReader;

/**
 * Provide users to fill entities
 */
class UserProvider
{
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
     * Provider of users in form of an ArrayReader
     *
     * @return array of ArrayReader objects containing user values
     */
    public function userArrayReaderDataProvider(): array
    {
        return [
            [
                new ArrayReader(
                    [
                        'id' => 1,
                        'name' => 'Bill Gates',
                        'email' => 'gates@email.com',
                        'password' => '12345678',
                        'password2' => '12345678',
                        'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                        'role' => 'admin'
                    ]
                )
            ],
            [
                new ArrayReader(
                    [
                        'id' => 2,
                        'name' => 'Steve Jobs',
                        'email' => 'jobs@email.com',
                        'password' => '12345678',
                        'password2' => '12345678',
                        'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                        'role' => 'user'
                    ]
                )
            ],
            [
                new ArrayReader(
                    [
                        'id' => 3,
                        'name' => 'Mark Zuckerberg',
                        'email' => 'zuckerberg@email.com',
                        'password' => '12345678',
                        'password2' => '12345678',
                        'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                        'role' => 'user'
                    ]
                )
            ],
            [
                new ArrayReader(
                    [
                        'id' => 4,
                        'name' => 'Evan Spiegel',
                        'email' => 'spiegel@email.com',
                        'password' => '12345678',
                        'password2' => '12345678',
                        'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                        'role' => 'user'
                    ]
                )
            ],
            [
                new ArrayReader(
                    [
                        'id' => 5,
                        'name' => 'Jack Dorsey',
                        'email' => 'dorsey@email.com',
                        'password' => '12345678',
                        'password2' => '12345678',
                        'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                        'role' => 'user'
                    ]
                )
            ],
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
     * @return array
     */
    public function invalidUserProvider(): array
    {
        return [
            // Not existing user
            [
                [
                    'id' => 100000000,
                    'name' => 'B',
                    'email' => 'gates@email.com',
                    'password' => '12345678',
                    'password2' => '12345678',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'admin'
                ]
            ],
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
            // Required values not set
            [
                [
                    'id' => 1,
                    'name' => '',
                    'email' => '',
                    'password' => '',
                    'password2' => '',
                    'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
                    'role' => 'user'
                ]
            ],
        ];
        // Could add more rows with always 1 required missing because now error could be thrown
        // by another missing field.
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
     * Same as userArrayReaderDataProvider but can control amount of entries in array
     * and takes the users from a pool
     *
     * Provider of users in form of an ArrayReader
     *
     * @return array
     */
    /*public function userArrayReaderDataProvider(): array
    {
        $userValues = $this->sampleUsers;

        // Amount of times each test will be executed with different data
        $maxAmountOfValuesToProvide = 5;
        $i = 0;
        $returnObjects = [];
        foreach ($userValues as $userValue){
            // User value has to be in additional array for dataProvider for the case where multiple arguments have to be passed through
            $returnObjects[] = new ArrayReader($userValue);

            $i++;
            if ($maxAmountOfValuesToProvide >= $i){
                break;
            }
        }
        return [$returnObjects];
    }*/


}