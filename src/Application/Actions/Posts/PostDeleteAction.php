<?php

namespace App\Application\Actions\Posts;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Post\Post;
use App\Domain\Post\PostService;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
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
    protected PostService $postService;
    protected OutputEscapeService $outputEscapeService;
    protected AuthService $authService;
    protected LoggerInterface $logger;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param PostService $postService
     * @param OutputEscapeService $outputEscapeService
     * @param AuthService $authService
     * @param LoggerInterface $logger
     */
    public function __construct(
        Responder $responder,
        PostService $postService,
        OutputEscapeService $outputEscapeService,
        AuthService $authService,
        LoggerInterface $logger

    ) {
        $this->responder = $responder;
        $this->postService = $postService;
        $this->outputEscapeService = $outputEscapeService;
        $this->authService = $authService;
        $this->logger = $logger;
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

        $post = $this->postService->findPost($id);

        $userRole = $this->authService->getUserRole($userId);

        // Check if it's admin or if it's its own post
        if ($userRole === 'admin' || (int)$post['user_id'] === $userId) {
            $deleted = $this->postService->deletePost($id);
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