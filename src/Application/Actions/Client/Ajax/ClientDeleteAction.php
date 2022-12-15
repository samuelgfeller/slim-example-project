<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Client\Service\ClientDeleter;
use App\Domain\Factory\LoggerFactory;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Action.
 */
final class ClientDeleteAction
{
    protected LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ClientDeleter $clientDeleter
     * @param SessionInterface $session
     * @param LoggerFactory $logger
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly ClientDeleter $clientDeleter,
        private readonly SessionInterface $session,
        LoggerFactory $logger
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('client-delete');
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @throws \JsonException
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $clientId = (int)$args['client_id'];

            try {
                // Delete client logic
                $deleted = $this->clientDeleter->deleteClient($clientId);

                $flash = $this->session->getFlash();

                if ($deleted) {
                    // Add flash here as user gets redirected to client list after deletion
                    $flash->add('success', 'Successfully deleted client.');

                    return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
                }

                $response = $this->responder->respondWithJson(
                    $response,
                    ['status' => 'warning', 'message' => 'Client not deleted.']
                );
                // If not deleted, inform user
                $flash->add('warning', 'The client was not deleted');

                return $response->withAddedHeader('Warning', 'The client was not deleted');
            } catch (ForbiddenException $fe) {
                // Log event as this should not be able to happen with normal use. User has to manually make exact request
                $this->logger->notice(
                    '403 Forbidden, user ' . $loggedInUserId . ' tried to delete other client with id: ' . $clientId
                );
                // Not throwing HttpForbiddenException as it's a json request and response should be json too
                return $this->responder->respondWithJson(
                    $response,
                    ['status' => 'error', 'message' => 'Not allowed to delete client.'],
                    StatusCodeInterface::STATUS_FORBIDDEN
                );
            }
        }

        // UserAuthenticationMiddleware handles redirect to login
        return $response;
    }
}
