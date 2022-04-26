<?php


namespace App\Domain\Post\Service;


use App\Domain\Post\Data\PostData;
use App\Domain\Post\Data\UserPostData;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\Post\PostFinderRepository;

class PostFinder
{
    public function __construct(
        private PostFinderRepository $postFinderRepository,
        private PostUserRightSetter $postUserRightSetter,
    ) {
    }

    /**
     * Gives all undeleted posts from db with name of user
     *
     * @return PostData[]
     */
    public function findAllPostsWithUsers(): array
    {
        $allPosts = $this->postFinderRepository->findAllPostsWithUsers();
        $this->changeDateFormat($allPosts);
        // In PHP, an object variable doesn't contain the object itself as value. It only contains an object identifier
        // meaning the reference is passed and changes are made on the original reference that can be used further
        // https://www.php.net/manual/en/language.oop5.references.php; https://stackoverflow.com/a/65805372/9013718
        $this->postUserRightSetter->setUserRightsOnPosts($allPosts);
        return $allPosts;
    }

    /**
     * Find one post in the database
     *
     * @param $id
     * @return PostData
     */
    public function findPost($id): PostData
    {
        return $this->postFinderRepository->findPostById($id);
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param int $userId
     * @return UserPostData[]
     */
    public function findAllPostsFromUser(int $userId): array
    {
        $allPosts = $this->postFinderRepository->findAllPostsByUserId($userId);
        $this->changeDateFormat($allPosts);
        $this->postUserRightSetter->setUserRightsOnPosts($allPosts);
        return $allPosts;
    }

    /**
     * Change created and updated date format from SQL datetime to
     * something we are used to see in Switzerland
     *
     * @param UserPostData[] $userPosts
     * @param string $format If default format changes, it has to be adapted in PostListActionTest
     *
     * @return void
     */
    private function changeDateFormat(array $userPosts, string $format = 'd.m.Y H:i:s'): void
    {
        // Tested in PostListActionTest
        foreach ($userPosts as $userPost) {
            // Change updated at format
            $userPost->postUpdatedAt = $userPost->postUpdatedAt ? date(
                $format,
                strtotime($userPost->postUpdatedAt)
            ) : null;
            // Change created at format
            $userPost->postCreatedAt = $userPost->postCreatedAt ? date(
                $format,
                strtotime($userPost->postCreatedAt)
            ) : null;
        }
    }
}