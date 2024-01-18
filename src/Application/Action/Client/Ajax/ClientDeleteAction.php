<?php

namespace App\Application\Action\Client\Ajax;

use App\Application\Renderer\JsonEncoder;
use App\Domain\Client\Service\ClientDeleter;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientDeleteAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private ClientDeleter $clientDeleter,
        private SessionInterface $session,
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

        // Delete client logic
        $deleted = $this->clientDeleter->deleteClient($clientId);

        $flash = $this->session->getFlash();

        if ($deleted) {
            // Add flash here as user gets redirected to client list after deletion
            $flash->add('success', __('Successfully deleted client.'));

            return $this->jsonEncoder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null]);
        }

        $response = $this->jsonEncoder->encodeAndAddToResponse(
            $response,
            ['status' => 'warning', 'message' => 'Client not deleted.']
        );
        // If not deleted, inform user
        $flash->add('warning', 'The client was not deleted');

        return $response->withAddedHeader('Warning', 'The client was not deleted');
    }
}
