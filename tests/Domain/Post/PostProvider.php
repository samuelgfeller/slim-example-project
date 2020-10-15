<?php


namespace App\Test\Domain\Post;


use App\Domain\Utility\ArrayReader;

class PostProvider
{
    public function getSamplePosts(): array
    {
        return [
            ['id' => 1, 'user_id' => 1, 'message' => 'This is the first test message'],
            ['id' => 2, 'user_id' => 2, 'message' => 'This is the second test message'],
            ['id' => 3, 'user_id' => 3, 'message' => 'This is the third test message'],
            ['id' => 4, 'user_id' => 4, 'message' => 'This is the fourth test message'],
            ['id' => 5, 'user_id' => 5, 'message' => 'This is the fifth test message'],
        ];
    }

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
     * Provide a set of users in a DataProvider format
     *
     * @return array of users
     */
    public function oneSetOfMultiplePostsProvider(): array
    {
        return [
          [
              'posts_without_user_name' => $this->getSamplePosts(),
              'posts_with_name_of_user' => $this->getSamplePosts()
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
     * @return array of ArrayReader Instances
     */
    public function invalidPostsProvider(): array
    {
        return [
            // Not existing user
            [['id' => 100000000, 'name' => 'B', 'email' => 'gates@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user']],
            // Name too short
            [['id' => 2, 'name' => 'B', 'email' => 'gates@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user']],
            // Invalid Email
            [['id' => 1, 'name' => 'Bill Gates', 'email' => 'gates@ema$il.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user']],
            // Not matching Passwords
            [['id' => 1, 'name' => 'Bill Gates', 'email' => 'gates@email.com', 'password' => '123456789', 'password2' => '12345678', 'role' => 'user']],
            // Required values not set
            [['id' => 1, 'name' => '', 'email' => '', 'password' => '', 'password2' => '', 'role' => 'user']],
        ];
        // Could add more rows with always 1 required missing because now error could be thrown
        // by another missing field.
    }


}