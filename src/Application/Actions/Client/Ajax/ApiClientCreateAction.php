<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Client\Service\ClientCreatorFromClientSubmit;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $clientValues = $request->getParsedBody();

        $insertId = $this->clientCreatorFromClientSubmit->createClientFromClientSubmit($clientValues);

        if (0 !== $insertId) {
            return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null], 201);
        }

        $response = $this->responder->respondWithJson($response, [
            'status' => 'warning',
            'message' => 'Client not created',
        ]);

        return $response->withAddedHeader('Warning', 'The client could not be created');
    }
}
