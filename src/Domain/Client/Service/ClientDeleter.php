<?php


namespace App\Domain\Client\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Client\ClientDeleterRepository;
use App\Infrastructure\Post\PostDeleterRepository;

class ClientDeleter
{
    public function __construct(
        private readonly ClientDeleterRepository  $clientDeleterRepository,
        private readonly ClientFinder             $clientFinder,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
    ) { }

    /**
     * Delete one post logic
     *
     * @param int $postId
     * @param int $loggedInUserId
     * @return bool
     * @throws ForbiddenException
     */
    public function deleteClient(int $postId, int $loggedInUserId): bool
    {
        // Find post in db to get its ownership
        $clientFromDb = $this->clientFinder->findClient($postId);

        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);

        // Check if it's admin or if it's its own post
        if ($userRole === 'admin' || $clientFromDb->user_id === $loggedInUserId) {
            return $this->clientDeleterRepository->deleteClient($postId);
        }
        throw new ForbiddenException('You have to be admin or the post creator to update this post');
    }
}