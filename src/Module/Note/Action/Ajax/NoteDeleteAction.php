<?php

namespace App\Module\Note\Action\Ajax;

use App\Core\Application\Responder\JsonResponder;
use App\Module\Note\Domain\Service\NoteDeleter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class NoteDeleteAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private NoteDeleter $noteDeleter,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $noteId = (int)$args['note_id'];

        // Delete note logic
        $deleted = $this->noteDeleter->deleteNote($noteId);

        if ($deleted) {
            return $this->jsonResponder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null]);
        }

        $response = $this->jsonResponder->encodeAndAddToResponse(
            $response,
            ['status' => 'warning', 'message' => 'Note has not been deleted.']
        );

        return $response->withAddedHeader('Warning', 'The note was not deleted');
    }
}
