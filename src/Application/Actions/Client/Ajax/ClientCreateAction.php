<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Client\Service\ClientCreator;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Validation\OutputEscapeService;
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
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly ClientCreator $clientCreator,
        private readonly SessionInterface $session,
    ) {
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
            $clientValues = $request->getParsedBody();

            // If a html form name changes, these changes have to be done in the data class constructor
            // Check that request body syntax is formatted right (tested in ClientCreateActionTest - malformedRequest)
            // isset cannot be used in this context as it returns false if the key exists but is null, and we don't want
            // to test required fields here, just that the request body syntax is right
            if (null !== $clientValues && [] !== $clientValues && !array_diff([
                    'client_status_id',
                    'user_id',
                    'first_name',
                    'last_name',
                    'phone',
                    'location',
                    'birthdate',
                    'email',
                    'sex',
                ], array_keys($clientValues)) && (count($clientValues) === 9 ||
                    // client_message may be present in request body or not
                    (array_key_exists('client_message', $clientValues) && count($clientValues) === 10))) {
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
                            'message' => 'Not allowed to create a client.'
                        ],
                        403
                    );
                }

                if (0 !== $insertId) {
                    return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null], 201);
                }
                $response = $this->responder->respondWithJson($response, [
                    'status' => 'warning',
                    'message' => 'Post not created'
                ]);
                return $response->withAddedHeader('Warning', 'The post could not be created');
            }
            throw new HttpBadRequestException($request, 'Request body malformed.');
        }

        // Handled by AuthenticationMiddleware
        return $response;
    }
}
