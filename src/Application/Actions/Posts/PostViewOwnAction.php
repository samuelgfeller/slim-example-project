<?php

namespace App\Application\Actions\Posts;

use App\Application\Responder\Responder;
use App\Domain\Post\Service\PostFinder;
use App\Domain\Validation\OutputEscapeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class PostViewOwnAction
{
    /**
     * @var Responder
     */
    private Responder $responder;
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
        // option 1 /posts?user=xxx and then $request->getQueryParams('user'); but that would mean that the user has to know its id
        // option 2 /own-posts and get user id from token data body

        $loggedUserId = (int)$this->getUserIdFromToken($request);

        $posts = $this->postFinder->findAllPostsFromUser($loggedUserId);

        $posts = $this->outputEscapeService->escapeTwoDimensionalArray($posts);

        return $this->responder->respondWithJson($response, $posts);
    }
}
