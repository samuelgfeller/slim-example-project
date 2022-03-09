<?php

namespace App\Test\Provider\Post;

use App\Domain\Post\Data\PostData;
use App\Domain\Post\Data\UserPostData;
use App\Domain\User\Data\UserData;

class PostDataProvider
{
    public array $samplePosts = [
        ['id' => 1, 'user_id' => 1, 'message' => 'This is the first test message'],
        ['id' => 2, 'user_id' => 2, 'message' => 'This is the second test message'],
        ['id' => 3, 'user_id' => 3, 'message' => 'This is the third test message'],
        ['id' => 4, 'user_id' => 4, 'message' => 'This is the fourth test message'],
        ['id' => 5, 'user_id' => 5, 'message' => 'This is the fifth test message'],
    ];

    /**
     * Most of the functions returning posts are expected to automatically
     * populate the Post object with its according user
     * @return UserData
     */
    private function getGenericUser(): UserData
    {
        return new UserData([
                                'id' => 1,
                                'first_name' => 'John',
                                'surname' => 'Wick',
                                'email' => 'john@wick.com',
                                'password_hash' => password_hash('12345678', PASSWORD_DEFAULT),
                                'status' => UserData::STATUS_ACTIVE,
                                'role' => 'admin',
                            ]);
    }

    /**
     * Provide a set of posts attached to same user in a DataProvider format
     *
     * @return UserPostData[][][]
     */
    public function oneSetOfMultipleUserPostsProvider(): array
    {
        // Array that is expected for repository functions like findAllPostsWithUsers()
        return [
            [
                // Called with [0]['posts'] in PostFilterFinderTest.php
                'posts' => [
                    new UserPostData([
                                         'post_id' => 1,
                                         'user_id' => 1,
                                         'post_message' => 'This is the first test message',
                                         'post_created_at' => date('Y-m-d H:i:s'),
                                         'post_updated_at' => date('Y-m-d H:i:s'),
                                         'user_name' => 'Admin Example',
                                         'user_role' => 'admin',
                                     ]),
                    new UserPostData([
                                         'post_id' => 2,
                                         'user_id' => 1,
                                         'post_message' => 'This is the second test message',
                                         'post_created_at' => date('Y-m-d H:i:s'),
                                         'post_updated_at' => date('Y-m-d H:i:s'),
                                         'user_name' => 'Admin Example',
                                         'user_role' => 'admin',
                                     ]),

                ],
            ],
        ];
    }

    /**
     * Provide one user in a DataProvider format
     *
     * @return array<array<array>>
     */
    public function onePostProvider(): array
    {
        return [
            [
                ['id' => 1, 'user_id' => 1, 'message' => 'Test message', 'created_at' => date('Y-m-d H:i:s')],
            ]
        ];
    }

    /**
     * Unit test invalid post provider
     *
     * @return array<array<array>> invalid post data
     */
    public function invalidPostsProvider(): array
    {
        $tooLongMsg = 'iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii';
        return [
            // Msg too short (>4)
            [['id' => 1, 'user_id' => 1, 'message' => 'aaa']],
            // Msg too long (<500)
            [['id' => 1, 'user_id' => 1, 'message' => $tooLongMsg]],
            // Required msg empty
            [['id' => 1, 'user_id' => 1, 'message' => '']],
            // Required user_id missing
            [['id' => 1, 'user_id' => '', 'message' => '']],
        ];
        // Could add more rows with always 1 required missing because now error could be thrown
        // by another missing field.
    }


}