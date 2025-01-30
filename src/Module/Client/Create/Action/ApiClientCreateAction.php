<?php

namespace App\Module\Client\Create\Action;

use App\Application\Responder\JsonResponder;
use App\Module\Client\Create\Service\ClientCreatorFromApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Api action to create client.
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
        array $args,
    ): ResponseInterface {
        $clientValues = (array)$request->getParsedBody();

        $insertId = $this->clientCreatorFromClientSubmit->createClientFromApi($clientValues);

        if (0 !== $insertId) {
            return $this->jsonResponder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null], 201);
        }

        return $this->jsonResponder->encodeAndAddToResponse($response, [
            'status' => 'warning',
            'message' => 'Client was not created',
        ]);
    }
}
