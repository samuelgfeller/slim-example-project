<?php


namespace App\Domain\Client\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Client\Data\ClientData;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Post\PostUpdaterRepository;
use Psr\Log\LoggerInterface;

class ClientUpdater
{
    private LoggerInterface $logger;

    public function __construct(
        private ClientValidator          $postValidator,
        private PostUpdaterRepository    $postUpdaterRepository,
        private UserRoleFinderRepository $userRoleFinderRepository,
        private ClientFinder             $postFinder,
        LoggerFactory                    $logger

    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('post-service');
    }

    /**
     * Change something or multiple things on post
     *
     * @param int $postId id of post being changed
     * @param array|null $postValues values that have to be changed
     * @param int $loggedInUserId
     * @return bool if update was successful
     */
    public function updatePost(int $postId, null|array $postValues, int $loggedInUserId): bool
    {
        // Init object for validation
        $post = new ClientData($postValues);
        $this->postValidator->validatePostUpdate($post);

        // Find post in db to compare its ownership
        $postFromDb = $this->postFinder->findPost($postId);

        // I write the role logic always for each function and not a general service "isAuthorised" function because it's too different every time
        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);
        // Check if it's admin or if it's its own post
        if ($userRole === 'admin' || $postFromDb->userId === $loggedInUserId) {
            // The only thing that a user can change on a post is its message
            if (null !== $post->message) {
                // To be sure that only the message will be updated
                $updateData['message'] = $post->message;
                return $this->postUpdaterRepository->updatePost($updateData, $postId);
            }
            // Nothing was updated as message was empty
            return false;
        }
        // User does not have needed rights to access area or function
        $this->logger->notice(
            'User ' . $loggedInUserId . ' tried to update other post with id: ' . $loggedInUserId
        );
        throw new ForbiddenException('Not allowed to change that post as it\'s linked to another user.');
    }
}