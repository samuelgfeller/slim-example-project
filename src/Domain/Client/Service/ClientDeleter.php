<?php


namespace App\Domain\Client\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Post\PostDeleterRepository;

class ClientDeleter
{
    public function __construct(
        private PostDeleterRepository    $postDeleterRepository,
        private ClientFinder             $postFinder,
        private UserRoleFinderRepository $userRoleFinderRepository,
    ) { }

    /**
     * Delete one post logic
     *
     * @param int $postId
     * @param int $loggedInUserId
     * @return bool
     * @throws ForbiddenException
     */
    public function deletePost(int $postId, int $loggedInUserId): bool
    {
        // Find post in db to get its ownership
        $postFromDb = $this->postFinder->findPost($postId);

        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);

        // Check if it's admin or if it's its own post
        if ($userRole === 'admin' || $postFromDb->userId === $loggedInUserId) {
            return $this->postDeleterRepository->deletePost($postId);
        }
        throw new ForbiddenException('You have to be admin or the post creator to update this post');
    }
}