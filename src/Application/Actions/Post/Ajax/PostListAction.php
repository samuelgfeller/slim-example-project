<?php

namespace App\Application\Actions\Post\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Post\Exception\InvalidPostFilterException;
use App\Domain\Post\Service\PostFilterFinder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Post list all and own action.
 */
final class PostListAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param PostFilterFinder $postFilterFinder
     */
    public function __construct(
        private Responder $responder,
        private PostFilterFinder $postFilterFinder,
    ) {
    }

    /**
     * Post list all and own Action.
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
        try {
            // Retrieve posts with given filter values (or none)
            $userPosts = $this->postFilterFinder->findPostsWithFilter($request->getQueryParams());
        } catch (InvalidPostFilterException $invalidPostFilterException) {
            return $this->responder->respondWithJson(
                $response,
                // Response format tested in PostFilterProvider.php
                [
                    'status' => 'error',
                    'message' => $invalidPostFilterException->getMessage()
                ],
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        } // If user requests its own posts he has to be logged in
        catch (UnauthorizedException $unauthorizedException) {
            // Respond with status code 401 Unauthorized which is caught in the Ajax call
            return $this->responder->respondWithJson(
                $response,
                [
                    'loginUrl' => $this->responder->urlFor(
                        'login-page',
                        [],
                        ['redirect' => $this->responder->urlFor('post-list-own-page')])
                ],
                401
            );
        }
        return $this->responder->respondWithJson($response, $userPosts);
    }
}
