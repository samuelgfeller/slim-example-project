<?php

namespace App\Application\Action\Client\Ajax;

use App\Application\Responder\JsonEncoder;
use App\Domain\Client\Exception\InvalidClientFilterException;
use App\Domain\Client\Service\ClientFinderWithFilter;
use App\Test\Integration\Client\ClientListActionTest;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientFetchListAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private ClientFinderWithFilter $clientFilterFinder,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            // Retrieve clients with given filter values (or none)
            $clientResultCollection = $this->clientFilterFinder->findClientsWithFilter($request->getQueryParams());

            return $this->jsonEncoder->encodeAndAddToResponse($response, $clientResultCollection);
        } catch (InvalidClientFilterException $invalidClientFilterException) {
            return $this->jsonEncoder->encodeAndAddToResponse(
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
