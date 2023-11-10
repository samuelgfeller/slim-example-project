<?php

namespace App\Application\Action\Client\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\Client\Service\ClientCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ClientCreateAction
{
    public function __construct(
        private readonly JsonResponder $jsonResponder,
        private readonly ClientCreator $clientCreator,
    ) {
    }

    /**
     * Action.
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
        $clientValues = (array)$request->getParsedBody();

        // Validation and Forbidden exception caught in respective middlewares
        $insertId = $this->clientCreator->createClient($clientValues);

        if (0 !== $insertId) {
            return $this->jsonResponder->respondWithJson($response, ['status' => 'success', 'data' => null], 201);
        }
        $response = $this->jsonResponder->respondWithJson($response, [
            'status' => 'warning',
            'message' => 'Client not created',
        ]);

        return $response->withAddedHeader('Warning', 'The client could not be created');
    }
}