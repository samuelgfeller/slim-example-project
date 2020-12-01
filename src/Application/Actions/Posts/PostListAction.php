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
final class PostListAction
{
    /**
     * @var Responder
     */
    private Responder $responder;
    protected PostService $postService;
    protected OutputEscapeService $outputEscapeService;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param PostService $postService
     * @param OutputEscapeService $outputEscapeService
     */
    public function __construct(
        Responder $responder,
        PostService $postService,
        OutputEscapeService $outputEscapeService
    ) {
        $this->responder = $responder;
        $this->postService = $postService;
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
        $postsWithUsers = $this->postService->findAllPosts();

        // output escaping only done here https://stackoverflow.com/a/20962774/9013718
        $postsWithUsers = $this->outputEscapeService->escapeTwoDimensionalArray($postsWithUsers);

        return $this->responder->respondWithJson($response, $postsWithUsers);
    }
}
