<?php

namespace App\Application\Actions\Post\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Post\Service\PostFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class PostReadAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param PostFinder $postFinder
     */
    public function __construct(
        private Responder $responder,
        private PostFinder $postFinder,
    ) {
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
        $postWithUser = $this->postFinder->findPostWithUserById((int)$args['post_id']);

        // json_encode transforms object with public attributes to camelCase which matches Google recommendation
        // https://stackoverflow.com/a/19287394/9013718
        return $this->responder->respondWithJson($response, $postWithUser);
    }
}
