<?php


namespace App\Test\Provider\Client;


use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;

class ClientReadCaseProvider
{
    use FixtureTrait;

    /**
     * Provides where condition for logged-in users and user that is linked to note
     *
     * @return array
     */
    public function provideUserWhereConditionForNote(): array
    {
        $userData = $this->findRecordsFromFixtureWhere(['role' => 'user'], UserFixture::class)[0];
        return [
            [ // Authenticated user is ressource owner - non admin
                // User to whom the note is linked
                'owner_user' => $userData,
                'authenticated_user' => $userData,
                'expected_result' => [
                    'creation' => [
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                    ],
                    'modification' => [

                    ],
                    'deletion' => [

                    ],
                ],
            ],
            [ // Authenticated user is admin - non ressource owner
                'owner_user' => $userData,
                'authenticated_user' => $this->findRecordsFromFixtureWhere(['role' => 'admin'], UserFixture::class)[0],
                'expected_result' => [
                    'creation' => [
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                    ],
                ],
            ],
            [ // Authenticated user is not the ressource owner and not admin
                'owner_user' => $userData,
                // Get user with role user that is not the same then $userData
                'authenticated_user' => $this->findRecordsFromFixtureWhere(
                    ['role' => 'user'],
                    UserFixture::class,
                    ['id' => $userData['id']]
                )[0],
                'expected_result' => [
                    'creation' => [
                        // Should be created as users that are not linked to client are able to create notes - this will be different for mutation
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns combinations of invalid data to trigger validation exception.
     *
     * @return array
     */
    public function providePostCreateInvalidData(): array
    {
        // Message over 500 chars
        $tooLongMsg = 'iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii';
        return [
            [
                'message_too_short' => ['message' => 'Me'],
                'validation_error_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the post data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'message',
                                'message' => 'Required minimum length is 4',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'message_too_long' => ['message' => $tooLongMsg],
                'validation_error_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the post data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'message',
                                'message' => 'Required maximum length is 500',
                            ]
                        ]
                    ]
                ]
            ],

        ];
    }

}