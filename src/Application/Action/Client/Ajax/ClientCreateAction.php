<?php

namespace App\Application\Action\Client\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\Client\Service\ClientCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientCreateAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private ClientCreator $clientCreator,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $clientValues = (array)$request->getParsedBody();

        $insertId = $this->clientCreator->createClient($clientValues);

        if (0 !== $insertId) {
            return $this->jsonResponder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null], 201);
        }

        return $this->jsonResponder->encodeAndAddToResponse($response, [
            'status' => 'warning',
            'message' => 'Client not created',
        ]);
    }
}
