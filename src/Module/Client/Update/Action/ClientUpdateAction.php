<?php

namespace App\Module\Client\Update\Action;

use App\Core\Application\Responder\JsonResponder;
use App\Module\Client\Update\Service\ClientUpdater;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientUpdateAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private ClientUpdater $clientUpdater,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $clientId = (int)$args['client_id'];
        $clientValues = (array)$request->getParsedBody();
        $updateData = $this->clientUpdater->updateClient($clientId, $clientValues);

        if ($updateData['updated']) {
            return $this->jsonResponder->encodeAndAddToResponse(
                $response,
                ['status' => 'success', 'data' => $updateData['data']]
            );
        }
        $response = $this->jsonResponder->encodeAndAddToResponse($response, [
            'status' => 'warning',
            'message' => 'The client was not updated.',
            'data' => $updateData['data'],
        ]);

        return $response->withAddedHeader('Warning', 'The client was not updated.');
    }
}
