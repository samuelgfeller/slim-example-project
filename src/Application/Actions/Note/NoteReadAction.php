<?php

namespace App\Application\Actions\Note;

use App\Application\Responder\Responder;
use App\Domain\Note\Service\NoteFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class NoteReadAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param NoteFinder $noteFinder
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly NoteFinder $noteFinder,
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
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $noteData = $this->noteFinder->findNote((int)$args['note_id']);
        if ($noteData->id) {
            return $this->responder->redirectToUrl(
                $response,
                $this->responder->urlFor('client-read-page', ['client_id' => $noteData->clientId]) .
                "#note-$noteData->id-container"
            );
        }
        // When note does not exist link to client list page
        return $this->responder->redirectToRouteName($response, 'client-list-page');
    }
}
