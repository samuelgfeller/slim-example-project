<?php

namespace App\Application\Action\Note\Ajax;

use App\Application\Renderer\JsonEncoder;
use App\Domain\Note\Service\NoteDeleter;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class NoteDeleteAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private NoteDeleter $noteDeleter,
        private SessionInterface $session,
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
            return $this->jsonEncoder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null]);
        }

        $response = $this->jsonEncoder->encodeAndAddToResponse(
            $response,
            ['status' => 'warning', 'message' => 'Note not deleted.']
        );
        $flash = $this->session->getFlash();
        // If not deleted, inform user
        $flash->add('warning', __('The note was not deleted'));

        return $response->withAddedHeader('Warning', 'The note was not deleted');
    }
}
