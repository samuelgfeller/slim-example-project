<?php

namespace App\Application\Action\Client\Ajax;

use App\Application\Renderer\JsonEncoder;
use App\Domain\Client\Service\ClientCreatorFromApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Api Action.
 */
final readonly class ApiClientCreateAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private ClientCreatorFromApi $clientCreatorFromClientSubmit,
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
        $clientValues = (array)$request->getParsedBody();

        $insertId = $this->clientCreatorFromClientSubmit->createClientFromClientSubmit($clientValues);

        if (0 !== $insertId) {
            return $this->jsonEncoder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null], 201);
        }

        $response = $this->jsonEncoder->encodeAndAddToResponse($response, [
            'status' => 'warning',
            'message' => 'Client not created',
        ]);

        return $response->withAddedHeader('Warning', 'The client could not be created');
    }
}
