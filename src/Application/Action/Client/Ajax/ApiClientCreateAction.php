<?php

namespace App\Application\Action\Client\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\Client\Service\ClientCreatorFromApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Api action for external domains.
 */
final readonly class ApiClientCreateAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private ClientCreatorFromApi $clientCreatorFromClientSubmit,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $clientValues = (array)$request->getParsedBody();

        $insertId = $this->clientCreatorFromClientSubmit->createClientFromClientSubmit($clientValues);

        if (0 !== $insertId) {
            return $this->jsonResponder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null], 201);
        }

        $response = $this->jsonResponder->encodeAndAddToResponse($response, [
            'status' => 'warning',
            'message' => 'Client not created',
        ]);

        return $response->withAddedHeader('Warning', 'The client could not be created');
    }
}
