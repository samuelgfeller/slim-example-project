<?php

namespace App\Application\Action\Client\Ajax;

use App\Application\Renderer\JsonEncoder;
use App\Domain\Client\Service\ClientUpdater;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientUpdateAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private ClientUpdater $clientUpdater,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $clientId = (int)$args['client_id'];
        $clientValues = (array)$request->getParsedBody();
        $updateData = $this->clientUpdater->updateClient($clientId, $clientValues);

        if ($updateData['updated']) {
            return $this->jsonEncoder->encodeAndAddToResponse(
                $response,
                ['status' => 'success', 'data' => $updateData['data']]
            );
        }
        $response = $this->jsonEncoder->encodeAndAddToResponse($response, [
            'status' => 'warning',
            'message' => 'The client was not updated.',
            'data' => $updateData['data'],
        ]);

        return $response->withAddedHeader('Warning', 'The client was not updated.');
    }
}
