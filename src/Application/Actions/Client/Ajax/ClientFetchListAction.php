<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Client\Exception\InvalidClientFilterException;
use App\Domain\Client\Service\ClientFinderWithFilter;
use App\Test\Integration\Client\ClientListActionTest;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ClientFetchListAction
{
    public function __construct(
        private readonly Responder $responder,
        private readonly ClientFinderWithFilter $clientFilterFinder,
    ) {
    }

    /**
     * Client fetch list Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
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
                /** @see ClientListActionTest::testClientListActionInvalidFilters() */
                [
                    'status' => 'error',
                    'message' => $invalidClientFilterException->getMessage(),
                ],
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        }
    }
}
