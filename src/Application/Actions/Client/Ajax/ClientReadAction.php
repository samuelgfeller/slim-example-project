<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Client\Service\ClientFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

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
     * @param array $args
     *
     * @throws \JsonException
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $clientAggregate = $this->clientFinder->findClientReadAggregate((int)$args['client_id']);

            // json_encode transforms object with public attributes to camelCase which matches Google recommendation
            // https://stackoverflow.com/a/19287394/9013718
            return $this->responder->respondWithJson($response, $clientAggregate);
        } catch (ForbiddenException $forbiddenException) {
            throw new HttpForbiddenException($request, $forbiddenException->getMessage());
        }
    }
}
