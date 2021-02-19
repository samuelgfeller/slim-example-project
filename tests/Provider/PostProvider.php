<?php

namespace App\Test\Provider;

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
     * Provide a set of posts in a DataProvider format
     *
     * @return array of posts
     */
    public function oneSetOfMultiplePostsProvider(): array
    {
        return [
            [
                $this->samplePosts,
            ],
        ];
    }

    /**
     * Provide one user in a DataProvider format
     *
     * @return array
     */
    public function onePostProvider(): array
    {
        return [
            [
                ['id' => 1, 'user_id' => 1, 'message' => 'This is the first test message'],
            ]
        ];
    }

    /**
     * @return array
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