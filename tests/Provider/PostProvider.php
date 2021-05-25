<?php

namespace App\Test\Provider;

use App\Domain\Post\DTO\Post;
use App\Domain\Post\DTO\UserPost;
use App\Domain\User\DTO\User;
use JetBrains\PhpStorm\ArrayShape;

class PostProvider
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
     * @return User
     */
    private function getGenericUser(): User
    {
        return new User(
            [
                'id' => 1,
                'name' => 'John Wick',
                'email' => 'john@wick.com',
                'password_hash' => password_hash('12345678', PASSWORD_DEFAULT),
                'status' => User::STATUS_ACTIVE,
                'role' => 'admin',
            ]
        );
    }

    /**
     * Provide a set of posts in a DataProvider format
     *
     * @return UserPost[][][]
     */
    public function oneSetOfMultipleUserPostsProvider(): array
    {
        // Array that is expected for repository functions like findAllPostsWithUsers()
        return [
            [
                'posts' => [
                    new UserPost(
                        [
                            'post_id' => 1,
                            'user_id' => 1,
                            'post_message' => 'This is the first test message',
                            'post_created_at' => date('Y-m-d H:i:s'),
                            'post_updated_at' => date('Y-m-d H:i:s'),
                            'user_name' => 'Admin Example',
                            'user_role' => 'admin',
                        ]
                    ),
                    new UserPost(
                        [
                            'post_id' => 2,
                            'user_id' => 1,
                            'post_message' => 'This is the second test message',
                            'post_created_at' => date('Y-m-d H:i:s'),
                            'post_updated_at' => date('Y-m-d H:i:s'),
                            'user_name' => 'Admin Example',
                            'user_role' => 'admin',
                        ]
                    ),

                ],
            ],
        ];
    }

    /**
     * Provide one user in a DataProvider format
     *
     * @return array<array<Post>>
     */
    public function onePostProvider(): array
    {
        return [
            [
                new Post(
                    ['id' => 1, 'user_id' => 1, 'message' => 'Test message', 'created_at' => date('Y-m-d H:i:s')]
                ),
            ]
        ];
    }

    /**
     * @return Post[][]
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
            [new Post(['id' => 1, 'user_id' => 1, 'message' => 'aaa'])],
            // Msg too long (<500)
            [new Post(['id' => 1, 'user_id' => 1, 'message' => $tooLongMsg])],
            // Required msg empty
            [new Post(['id' => 1, 'user_id' => 1, 'message' => ''])],
            // Required user_id missing
            [new Post(['id' => 1, 'user_id' => '', 'message' => ''])],
        ];
        // Could add more rows with always 1 required missing because now error could be thrown
        // by another missing field.
    }


}