<?php

namespace App\Application\Actions\Note\Page;

use App\Application\Responder\Responder;
use App\Domain\Note\Service\NoteFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class NoteReadPageAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param NoteFinder $noteFinder
     * @param SessionInterface $session
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly NoteFinder $noteFinder,
        private readonly SessionInterface $session,
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
        $noteData = $this->noteFinder->findNote((int)$args['note_id']);
        if ($noteData->id) {
            // Redirect to client read page with hash anchor to the correct note container
            return $this->responder->redirectToUrl(
                $response,
                $this->responder->urlFor('client-read-page', ['client_id' => $noteData->clientId]) .
                "#note-$noteData->id-container"
            );
        }
        $flash = $this->session->getFlash();
        // If not existing note, inform user
        $flash->add('error', 'The note was not not found.');
        // When note does not exist link to client list page
        return $this->responder->redirectToRouteName($response, 'client-list-page');
    }
}
