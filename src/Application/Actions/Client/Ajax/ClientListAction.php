<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Client\Service\ClientFilterFinder;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Post\Exception\InvalidPostFilterException;
use App\Domain\Post\Service\PostFilterFinder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Post list all and own action.
 */
final class ClientListAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ClientFilterFinder $clientFilterFinder
     */
    public function __construct(
        private Responder $responder,
        private readonly ClientFilterFinder $clientFilterFinder,
    ) {
    }

    /**
     * Client list all and own Action.
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
            $userPosts = $this->clientFilterFinder->findClientsWithFilter($request->getQueryParams());
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
                        ['redirect' => $this->responder->urlFor('client-list-assigned-to-me-page')])
                ],
                401
            );
        }
        return $this->responder->respondWithJson($response, $userPosts);
    }
}
