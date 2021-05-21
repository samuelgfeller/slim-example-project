<?php


namespace App\Infrastructure\Post;


use App\Common\Hydrator;
use App\Domain\Post\DTO\Post;
use App\Domain\User\DTO\User;
use App\Infrastructure\DataManager;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;

class PostFinderRepository
{

    public function __construct(
        private DataManager $dataManager,
        private Hydrator $hydrator
    ) { }

    /**
     * Return all posts with users attribute loaded
     *
     * @return Post[]
     */
    public function findAllPostsWithUsers(): array
    {
        $query = $this->dataManager->newQuery()->from('post');
        $query->select(
            [
                'post_message' => 'post.message',
                'post_updated_at' => 'post.updated_at',
                'post_created_at' => 'post.created_at',
                'user_name' => 'user.name',
                'user_email' => 'user.email',
                'user_role' => 'user.role',
                'user_status' => 'user.status',
            ]
        )->join(['table' => 'user', 'conditions' => 'post.user_id = user.id'])->andWhere(
            ['post.deleted_at IS' => null]
        );
        $resultRows = $query->execute()->fetchAll('assoc');
        // Convert to list of Post objects with associated User
        return $this->hydrator->hydrateAggregates($resultRows, [Post::class, 'post_'], [[User::class, 'user_', 'user']]);
    }

    /**
     * Return post with given id if it exists
     * otherwise null
     *
     * @param string|int $id
     * @return Post
     */
    public function findPostById(string|int $id): Post
    {
        $postRow = $this->dataManager->findById('post', $id);
        return new Post($postRow);
    }

    /**
     * Retrieve post from database
     * If not found error is thrown
     *
     * @param int $id
     * @return array
     * @throws PersistenceRecordNotFoundException
     */
    public function getPostById(int $id): array
    {
        return $this->dataManager->getById('post', $id);
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param int $userId
     * @return Post[]
     */
    public function findAllPostsByUserId(int $userId): array
    {
        $postRows = $this->dataManager->findAllBy('post', 'user_id', $userId);
        // Convert to list of objects
        return $this->hydrator->hydrate($postRows, Post::class);
    }
}