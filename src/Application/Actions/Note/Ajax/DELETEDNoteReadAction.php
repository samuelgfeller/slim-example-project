<?php

namespace App\Application\Actions\Note\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Note\Service\NoteFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class DELETEDNoteReadAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param NoteFinder $noteFinder
     */
    public function __construct(
        private Responder $responder,
        private NoteFinder $noteFinder,
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
        $noteWithUser = $this->noteFinder->findNote((int)$args['note_id']);

        // json_encode transforms object with public attributes to camelCase which matches Google recommendation
        // https://stackoverflow.com/a/19287394/9013718
        return $this->responder->respondWithJson($response, $noteWithUser);
    }
}
