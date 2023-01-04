<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Client\Service\ClientCreator;
use App\Domain\Validation\ValidationException;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * Action.
 */
final class ClientCreateAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ClientCreator $clientCreator
     * @param SessionInterface $session
     * @param MalformedRequestBodyChecker $malformedRequestBodyChecker
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly ClientCreator $clientCreator,
        private readonly SessionInterface $session,
        private readonly MalformedRequestBodyChecker $malformedRequestBodyChecker,
    ) {
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
            $clientValues = $request->getParsedBody();

            // If html form names change they have to be adapted in the data class attributes too (e.g. ClientData)
            // Check that request body syntax is formatted right (tested in ClientCreateActionTest - malformedRequest)
            if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys($clientValues, [
                'client_status_id',
                'user_id',
                'first_name',
                'last_name',
                'phone',
                'location',
                'message',
                'birthdate',
                'email',
            ], // Html radio buttons and checkboxes are not sent over by the client if they are not set hence optional
                ['sex', 'client_message', 'vigilance_level'])) {
                try {
                    $insertId = $this->clientCreator->createClient($clientValues);
                } catch (ValidationException $exception) {
                    return $this->responder->respondWithJsonOnValidationError(
                        $exception->getValidationResult(),
                        $response
                    );
                } catch (ForbiddenException $forbiddenException) {
                    return $this->responder->respondWithJson(
                        $response,
                        [
                            'status' => 'error',
                            'message' => 'Not allowed to create client.',
                        ],
                        StatusCodeInterface::STATUS_FORBIDDEN
                    );
                }

                if (0 !== $insertId) {
                    return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null], 201);
                }
                $response = $this->responder->respondWithJson($response, [
                    'status' => 'warning',
                    'message' => 'Client not created',
                ]);

                return $response->withAddedHeader('Warning', 'The post could not be created');
            }
            throw new HttpBadRequestException($request, 'Request body malformed.');
        }

        // Handled by AuthenticationMiddleware
        return $response;
    }
}
