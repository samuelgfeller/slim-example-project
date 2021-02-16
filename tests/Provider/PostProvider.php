<?php


namespace App\Test\Provider;


use App\Domain\Utility\ArrayReader;

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
     * Provider of users in form of an ArrayReader
     *
     * @return array of User objects
     */
//    public function userArrayReaderDataProvider(): array
//    {
//        return [
//            [new ArrayReader(['id' => 1, 'name' => 'Bill Gates', 'email' => 'gates@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'admin'])],
//            [new ArrayReader(['id' => 2, 'name' => 'Steve Jobs', 'email' => 'jobs@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'])],
//            [new ArrayReader(['id' => 3, 'name' => 'Mark Zuckerberg', 'email' => 'zuckerberg@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'])],
//            [new ArrayReader(['id' => 4, 'name' => 'Evan Spiegel', 'email' => 'spiegel@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'])],
//            [new ArrayReader(['id' => 5, 'name' => 'Jack Dorsey', 'email' => 'dorsey@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'])],
//        ];
//    }

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

    public function addNameOfUsersToPostsArray(array $posts)
    {
        $postsWithUser = [];
        foreach ($posts as $post) {
            // Get user information connected to post
            $user = $this->userService->findUser($post['user_id']);
            // If user was deleted but post not, post should not be shown since it is also technically deleted
            if (isset($user['name'])) {
                $post['user_name'] = $user['name'];
                $postsWithUser[] = $post;
            }
        }
        return $postsWithUser;
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