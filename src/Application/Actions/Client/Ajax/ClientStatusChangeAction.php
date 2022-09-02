<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Client\Service\ClientStatusUpdater;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * Action.
 */
final class ClientStatusChangeAction
{
    /**
     * @var Responder
     */
    private Responder $responder;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param SessionInterface $session
     * @param ClientStatusUpdater $clientStatusUpdater
     */
    public function __construct(
        Responder $responder,
        private readonly SessionInterface $session,
        private readonly ClientStatusUpdater $clientStatusUpdater,
    ) {
        $this->responder = $responder;
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args
     * @return ResponseInterface The response
     * @throws \JsonException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $clientStatusValues = $request->getParsedBody();

            // If a html form name changes, these changes have to be done in the data class constructor
            // Check that request body syntax is formatted right (if changed, )
            if (null !== $clientStatusValues && [] !== $clientStatusValues &&
                isset($clientStatusValues['client_status_id'], $clientStatusValues['client_id']) &&
                count($clientStatusValues) === 2) {
                // No try / catch as there is no validation
                $updated = $this->clientStatusUpdater->changeClientStatus(
                    (int)$clientStatusValues['client_id'],
                    (int)$clientStatusValues['client_status_id'],
                    (int)$loggedInUserId,
                );

                if ($updated !== false) {
                    return $this->responder->respondWithJson($response, ['status' => 'success'], 201);
                }
                $response = $this->responder->respondWithJson($response, [
                    'status' => 'warning',
                    'message' => 'Client status not updated'
                ]);
                return $response->withAddedHeader('Warning', 'Client status could not be updated');
            }
            throw new HttpBadRequestException($request, 'Request body malformed.');
        }

        // Handled by AuthenticationMiddleware
        return $response;
    }
}
