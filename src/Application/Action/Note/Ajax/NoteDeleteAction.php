<?php

namespace App\Application\Action\Note\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\Note\Service\NoteDeleter;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class NoteDeleteAction
{
    public function __construct(
        private readonly JsonResponder $jsonResponder,
        private readonly NoteDeleter $noteDeleter,
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
        $noteId = (int)$args['note_id'];

        // Delete note logic
        $deleted = $this->noteDeleter->deleteNote($noteId);

        if ($deleted) {
            return $this->jsonResponder->respondWithJson($response, ['status' => 'success', 'data' => null]);
        }

        $response = $this->jsonResponder->respondWithJson(
            $response,
            ['status' => 'warning', 'message' => 'Note not deleted.']
        );
        $flash = $this->session->getFlash();
        // If not deleted, inform user
        $flash->add('warning', __('The note was not deleted'));

        return $response->withAddedHeader('Warning', 'The note was not deleted');
    }
}
