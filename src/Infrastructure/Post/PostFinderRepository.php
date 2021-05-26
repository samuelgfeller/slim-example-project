<?php


namespace App\Infrastructure\Post;


use App\Common\Hydrator;
use App\Domain\Post\DTO\Post;
use App\Domain\Post\DTO\UserPost;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use App\Infrastructure\Factory\QueryFactory;

class PostFinderRepository
{

    public function __construct(
        private QueryFactory $queryFactory,
        private Hydrator $hydrator
    )
    {
    }

    /**
     * Return all posts with users attribute loaded
     *
     * @return UserPost[]
     */
    public function findAllPostsWithUsers(): array
    {
        $query = $this->queryFactory->newQuery()->from('post');
        $query->select(
            [
                'post_id' => 'post.id',
                'user_id' => 'user.id',
                'post_message' => 'post.message',
                'post_created_at' => 'post.created_at',
                'post_updated_at' => 'post.updated_at',
                'user_name' => 'user.name',
                'user_email' => 'user.email',
                'user_role' => 'user.role',
            ]
        )->join(['table' => 'user', 'conditions' => 'post.user_id = user.id'])->andWhere(
            ['post.deleted_at IS' => null]
        );
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Post objects with associated User info
        return $this->hydrator->hydrate($resultRows, UserPost::class);
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
        $query = $this->queryFactory->newQuery()->select(['*'])->from('post')->where(
            ['deleted_at IS' => null, 'id' => $id]);
        $postRow = $query->execute()->fetch('assoc') ?: [];
        return new Post($postRow);
    }

    /**
     * Return all posts with users attribute loaded
     *
     * @param int $id
     * @return UserPost
     */
    public function findUserPostById(int $id): UserPost
    {
        $query = $this->queryFactory->newQuery()->from('post');
        $query->select(
            [
                'post_id' => 'post.id',
                'user_id' => 'user.id',
                'post_message' => 'post.message',
                'post_created_at' => 'post.created_at',
                'post_updated_at' => 'post.updated_at',
                'user_name' => 'user.name',
                'user_role' => 'user.role',
            ]
        )->join(['table' => 'user', 'conditions' => 'post.user_id = user.id'])->andWhere(
            ['post.id' => $id, 'post.deleted_at IS' => null]
        );
        $resultRows = $query->execute()->fetch('assoc') ?: [];
        // Instantiate UserPost DTO
        return new UserPost($resultRows);
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
        $query = $this->queryFactory->newQuery()->select(['*'])->from('post')->where(
            ['deleted_at IS' => null, 'id' => $id]);
        $entry = $query->execute()->fetch('assoc');
        if (!$entry){
            throw new PersistenceRecordNotFoundException('post');
        }
        return $entry;
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param int $userId
     * @return UserPost[]
     */
    public function findAllPostsByUserId(int $userId): array
    {
        $query = $this->queryFactory->newQuery()->from('post');
        $query->select(
            [
                'post_id' => 'post.id',
                'user_id' => 'user.id',
                'post_message' => 'post.message',
                'post_created_at' => 'post.created_at',
                'post_updated_at' => 'post.updated_at',
                'user_name' => 'user.name',
                'user_role' => 'user.role',
            ]
        )->join(['table' => 'user', 'conditions' => 'post.user_id = user.id'])->andWhere(
            [
                'post.user_id' => $userId, // Not unsafe as its not an expression and thus escaped by querybuilder
                'post.deleted_at IS' => null
            ]
        );
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Post objects with associated User info
        return $this->hydrator->hydrate($resultRows, UserPost::class);
    }
}