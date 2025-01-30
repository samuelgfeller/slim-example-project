<?php

namespace App\Module\Client\List\Action;

use App\Application\Responder\JsonResponder;
use App\Module\Client\List\Domain\Exception\InvalidClientFilterException;
use App\Module\Client\List\Domain\Service\ClientFinderWithFilter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientFetchListAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private ClientFinderWithFilter $clientFilterFinder,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        try {
            // Retrieve clients with given filter values (or none)
            $clientResultCollection = $this->clientFilterFinder->findClientsWithFilter($request->getQueryParams());

            return $this->jsonResponder->encodeAndAddToResponse($response, $clientResultCollection);
        } catch (InvalidClientFilterException $invalidClientFilterException) {
            return $this->jsonResponder->encodeAndAddToResponse(
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
