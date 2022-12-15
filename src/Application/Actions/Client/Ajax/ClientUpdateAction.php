<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Client\Service\ClientUpdater;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\ValidationException;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * Action.
 */
final class ClientUpdateAction
{
    protected LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder
     * @param SessionInterface $session
     * @param ClientUpdater $clientUpdater
     * @param LoggerFactory $logger
     * @param MalformedRequestBodyChecker $malformedRequestBodyChecker
     */
    public function __construct(
        protected readonly Responder $responder,
        private readonly SessionInterface $session,
        private readonly ClientUpdater $clientUpdater,
        LoggerFactory $logger,
        private readonly MalformedRequestBodyChecker $malformedRequestBodyChecker,
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('client-update');
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
            $clientValues = $request->getParsedBody();
            // If request body empty, the update logic doesn't have to run
            if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys($clientValues, [], [
                'client_status_id',
                'user_id',
                'first_name',
                'last_name',
                'phone',
                'location',
                'birthdate',
                'email',
                'sex',
                'vigilance_level',
                'deleted_at',
            ])) {
                // Try to update client with given values
                try {
                    $updateData = $this->clientUpdater->updateClient($clientId, $clientValues, $loggedInUserId);

                    if ($updateData['updated']) {
                        return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => $updateData['data']]);
                    }
                    $response = $this->responder->respondWithJson($response, [
                        'status' => 'warning',
                        'message' => 'The client was not updated.',
                        'data' => $updateData['data'],
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
                        [
                            'status' => 'error',
                            'message' => 'Not allowed to update client.',
                        ],
                        StatusCodeInterface::STATUS_FORBIDDEN
                    );
                }
            }
            throw new HttpBadRequestException($request, 'Request body malformed.');
        }

        // Not logged in, let AuthenticationMiddleware handle redirect
        return $response;
    }
}
