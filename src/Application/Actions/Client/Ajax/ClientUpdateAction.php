<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Client\Service\ClientUpdater;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ClientUpdateAction
{
    public function __construct(
        protected readonly Responder $responder,
        private readonly ClientUpdater $clientUpdater,
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
        $clientId = (int)$args['client_id'];
        $clientValues = (array)$request->getParsedBody();
        $updateData = $this->clientUpdater->updateClient($clientId, $clientValues);

        if ($updateData['updated']) {
            return $this->responder->respondWithJson(
                $response,
                ['status' => 'success', 'data' => $updateData['data']]
            );
        }
        $response = $this->responder->respondWithJson($response, [
            'status' => 'warning',
            'message' => 'The client was not updated.',
            'data' => $updateData['data'],
        ]);

        return $response->withAddedHeader('Warning', 'The client was not updated.');
    }
}
