<?php

namespace App\Application\Actions\Post;

use App\Application\Responder\Responder;
use App\Domain\Post\Exception\InvalidPostFilterException;
use App\Domain\Post\Service\PostFilterFinder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class AllPostsListPageAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     */
    public function __construct(
        private Responder $responder,
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
     * @throws \Throwable
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // Loading the page. All posts are loaded dynamically with js after page load for a fast loading time
        return $this->responder->render($response, 'post/all-posts.html.php');
    }
}
