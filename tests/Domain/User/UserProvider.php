<?php


namespace App\Test\Domain\User;


use App\Domain\Utility\ArrayReader;

class UserProvider
{
    public function getSampleUsers(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Bill Gates',
                'email' => 'gates@email.com',
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
    }

    public function validUserProvider(): array
    {
        return [
            $this->getSampleUsers(),
        ];
    }
    /**
     * Provider of users in form of an ArrayReader
     *
     * @return array of User objects
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
            [$this->getSampleUsers()]
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
                    'role' => 'admin'
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
            [['id' => 1, 'name' => '', 'email' => '', 'password' => '', 'password2' => '',
                'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi', 'role' => 'user']],
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
        $userValues = $this->getSampleUsers();

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