<?php

namespace App\Application\Actions\Note\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Note\Service\NoteDeleter;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Action.
 */
final class NoteDeleteAction
{
    protected LoggerInterface $logger;

    public function __construct(
        private readonly Responder $responder,
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
            return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
        }

        $response = $this->responder->respondWithJson(
            $response,
            ['status' => 'warning', 'message' => 'Note not deleted.']
        );
        $flash = $this->session->getFlash();
        // If not deleted, inform user
        $flash->add('warning', __('The note was not deleted'));

        return $response->withAddedHeader('Warning', 'The note was not deleted');
    }
}
