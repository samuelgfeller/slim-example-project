<?php

namespace App\Application\Actions\Posts;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Post\PostService;
use App\Domain\Post\Service\PostDeleter;
use App\Domain\Post\Service\PostFinder;
use App\Domain\Validation\OutputEscapeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Action.
 */
final class PostDeleteAction
{
    /**
     * @var Responder
     */
    private Responder $responder;
    protected OutputEscapeService $outputEscapeService;
    protected AuthService $authService;
    protected LoggerInterface $logger;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param PostFinder $postFinder
     * @param PostDeleter $postDeleter
     * @param OutputEscapeService $outputEscapeService
     * @param AuthService $authService
     * @param LoggerFactory $logger
     */
    public function __construct(
        Responder $responder,
        private PostFinder $postFinder,
        private PostDeleter $postDeleter,
        OutputEscapeService $outputEscapeService,
        AuthService $authService,
        LoggerFactory $logger

    ) {
        $this->responder = $responder;
        $this->outputEscapeService = $outputEscapeService;
        $this->authService = $authService;
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('post-delete');
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args
     * @return ResponseInterface The response
     * @throws \JsonException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $userId = (int)$this->getUserIdFromToken($request);

        $id = (int)$args['id'];

        $post = $this->postFinder->findPost($id);

        $userRole = $this->authService->getUserRoleById($userId);

        // Check if it's admin or if it's its own post
        if ($userRole === 'admin' || $post->userId === $userId) {
            $deleted = $this->postDeleter->deletePost($id);
            if ($deleted) {
                return $this->responder->respondWithJson($response, ['status' => 'success']);
            }
            $response = $this->responder->respondWithJson(
                $response,
                ['status' => 'warning', 'message' => 'Post not deleted']
            );
            return $response->withAddedHeader('Warning', 'The post got not deleted');
        }
        $this->logger->notice('User ' . $userId . ' tried to delete other post with id: ' . $id);
        return $this->responder->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'You have to be admin or post creator to update this post'],
            403
        );
    }
}
