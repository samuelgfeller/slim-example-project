<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Client\Service\ClientFilterFinder;
use App\Domain\Client\Service\ClientFinder;
use App\Domain\Note\Service\NoteFinder;
use App\Infrastructure\Client\ClientDeleterRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class ClientReadAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ClientFinder $clientFinder
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly ClientFinder $clientFinder,
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
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $clientAggregate = $this->clientFinder->findClientAggregate((int)$args['client_id']);

        // json_encode transforms object with public attributes to camelCase which matches Google recommendation
        // https://stackoverflow.com/a/19287394/9013718
        return $this->responder->respondWithJson($response, $clientAggregate);
    }
}
