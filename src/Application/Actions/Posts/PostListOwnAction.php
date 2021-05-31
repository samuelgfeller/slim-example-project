<?php


namespace App\Application\Actions\Posts;


use App\Application\Responder\Responder;
use App\Domain\Post\Service\PostFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PostListOwnAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param PostFinder $postFinder
     * @param SessionInterface $session
     */
    public function __construct(
        private Responder $responder,
        private PostFinder $postFinder,
        private SessionInterface $session
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
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // URL format
        // option 1 /posts?user=xxx and then $request->getQueryParams('user'); but that would mean that the user has to know its id
        // option 2 /own-posts and get user id from token data body
        $postsWithUsers = $this->postFinder->findAllPostsFromUser((int)$this->session->get('user_id'));
        $a = $this->session->get('user_id');

        return $this->responder->respondWithJson($response, $postsWithUsers);
    }
}