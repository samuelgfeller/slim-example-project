<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Authorization\UnauthorizedException;
use App\Domain\Client\Exception\InvalidClientFilterException;
use App\Domain\Client\Service\ClientFinderWithFilter;
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
     * @param ClientFinderWithFilter $clientFilterFinder
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly ClientFinderWithFilter $clientFilterFinder,
    ) {
    }

    /**
     * Client list all and own Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @throws \JsonException
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            // Retrieve posts with given filter values (or none)
            $clientResultCollection = $this->clientFilterFinder->findClientsWithFilter($request->getQueryParams());

            return $this->responder->respondWithJson($response, $clientResultCollection);
        } catch (InvalidClientFilterException $invalidClientFilterException) {
            return $this->responder->respondWithJson(
                $response,
                // Response format tested in PostFilterProvider.php
                [
                    'status' => 'error',
                    'message' => $invalidClientFilterException->getMessage(),
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
                        ['redirect' => $this->responder->urlFor('client-list-assigned-to-me-page')]
                    ),
                ],
                401
            );
        }
    }
}
