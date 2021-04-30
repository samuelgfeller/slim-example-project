<?php

namespace App\Application\Actions\Posts;

use App\Application\Responder\Responder;
use App\Domain\Post\PostService;
use App\Domain\User\UserService;
use App\Domain\Validation\OutputEscapeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class PostViewAction
{
    /**
     * @var Responder
     */
    private Responder $responder;
    protected PostService $postService;
    protected UserService $userService;
    protected OutputEscapeService $outputEscapeService;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param PostService $postService
     * @param UserService $userService
     * @param OutputEscapeService $outputEscapeService
     */
    public function __construct(
        Responder $responder,
        PostService $postService,
        UserService $userService,
        OutputEscapeService $outputEscapeService
    ) {
        $this->responder = $responder;
        $this->postService = $postService;
        $this->userService = $userService;
        $this->outputEscapeService = $outputEscapeService;
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
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = $args['id'];
        $post = $this->postService->findPost($id);

        // Get user information connected to post
        $user = $this->userService->findUserById($post['user_id']);

        // Add user name info to post
        $postWithUser = $post;
        $postWithUser['user_name'] = $user['name'];

        $postWithUser = $this->outputEscapeService->escapeOneDimensionalArray($postWithUser);
        return $this->responder->respondWithJson($response, $postWithUser);    }
}
