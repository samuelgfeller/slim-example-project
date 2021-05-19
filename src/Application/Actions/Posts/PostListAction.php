<?php

namespace App\Application\Actions\Posts;

use App\Application\Responder\Responder;
use App\Domain\Post\PostFinder;
use App\Domain\Post\Service\PostFinder;
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
    protected PostFinder $postService;
    protected OutputEscapeService $outputEscapeService;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param PostFinder $postFinder
     * @param OutputEscapeService $outputEscapeService
     */
    public function __construct(
        Responder $responder,
        private PostFinder $postFinder,
        OutputEscapeService $outputEscapeService
    ) {
        $this->responder = $responder;
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
        $postsWithUsers = $this->postFinder->findAllPostsWithUsers();

        return $this->responder->respondWithJson($response, $postsWithUsers);
    }
}
