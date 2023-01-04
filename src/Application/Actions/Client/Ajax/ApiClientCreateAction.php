<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Client\Service\ClientCreatorFromClientSubmit;
use App\Domain\Validation\ValidationException;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * Api Action.
 */
final class ApiClientCreateAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ClientCreatorFromClientSubmit $clientCreatorFromClientSubmit
     * @param SessionInterface $session
     * @param MalformedRequestBodyChecker $malformedRequestBodyChecker
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly ClientCreatorFromClientSubmit $clientCreatorFromClientSubmit,
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
     * @throws \Exception
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $clientValues = $request->getParsedBody();

        // If html form names change they have to be adapted in the data class attributes too (e.g. ClientData)
        if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys(
            $clientValues,
            [
                'first_name',
                'last_name',
                'phone',
                'location',
                'birthdate',
                'email',
                'client_message',
            ], // Html radio buttons and checkboxes are not sent over by the client if they are not set hence optional
            ['sex']
        )) {
            try {
                $insertId = $this->clientCreatorFromClientSubmit->createClientFromClientSubmit($clientValues);
            } catch (ValidationException $exception) {
                return $this->responder->respondWithJsonOnValidationError(
                    $exception->getValidationResult(),
                    $response
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
}
