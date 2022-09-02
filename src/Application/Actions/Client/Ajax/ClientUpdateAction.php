<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Client\Service\ClientUpdater;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\OutputEscapeService;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Action.
 */
final class ClientUpdateAction
{
    /**
     * @var Responder
     */
    private Responder $responder;
    protected LoggerInterface $logger;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param SessionInterface $session
     * @param ClientUpdater $clientUpdater
     * @param LoggerFactory $logger
     * @param OutputEscapeService $outputEscapeService
     */
    public function __construct(
        Responder $responder,
        private readonly SessionInterface $session,
        private readonly ClientUpdater $clientUpdater,
        LoggerFactory $logger,
        OutputEscapeService $outputEscapeService,
    ) {
        $this->responder = $responder;
        $this->logger = $logger->addFileHandler('error.log')->createInstance('client-update');
        $this->outputEscapeService = $outputEscapeService;
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
            $clientId = (int)$args['client_id'];
            $clientValues = $request->getParsedBody();

            try {
                $updated = $this->clientUpdater->updateClient($clientId, $clientValues, $loggedInUserId);

                if ($updated) {
                    return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
                }
                $response = $this->responder->respondWithJson($response, [
                    'status' => 'warning',
                    'message' => 'The client was not updated.'
                ]);
                return $response->withAddedHeader('Warning', 'The client was not updated.');
            } catch (ValidationException $exception) {
                return $this->responder->respondWithJsonOnValidationError(
                    $exception->getValidationResult(),
                    $response
                );
            } catch (ForbiddenException $fe) {
                return $this->responder->respondWithJson(
                    $response,
                    ['status' => 'error', 'message' => 'You can only edit your own client or be an admin to edit others'],
                    403
                );
            }
        }

        // Not logged in, let AuthenticationMiddleware handle redirect
        return $response;
    }
}
