<?php

namespace App\Module\Note\Action\Page;

use App\Core\Application\Responder\RedirectHandler;
use App\Module\Note\Domain\Service\NoteFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteParserInterface;

final readonly class NoteReadPageAction
{
    public function __construct(
        private RedirectHandler $redirectHandler,
        private RouteParserInterface $routeParser,
        private NoteFinder $noteFinder,
        private SessionInterface $session,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $noteData = $this->noteFinder->findNote((int)$args['note_id']);
        if ($noteData->id) {
            // Redirect to client read page with hash anchor to the correct note container
            return $this->redirectHandler->redirectToUrl(
                $response,
                $this->routeParser->urlFor('client-read-page', ['client_id' => (string)$noteData->clientId]) .
                "#note-$noteData->id-container"
            );
        }
        $flash = $this->session->getFlash();
        // If not existing note, inform user
        $flash->add('error', __('The note was not not found.'));

        // When note does not exist link to client list page
        return $this->redirectHandler->redirectToRouteName($response, 'client-list-page');
    }
}
