<?php

namespace App\Application\Action\Note\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\Note\Service\NoteUpdater;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class NoteUpdateAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private NoteUpdater $noteUpdater,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $noteIdToChange = (int)$args['note_id'];
        $noteValues = (array)$request->getParsedBody();

        $updated = $this->noteUpdater->updateNote($noteIdToChange, $noteValues);

        if ($updated) {
            return $this->jsonResponder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null]);
        }
        $response = $this->jsonResponder->encodeAndAddToResponse($response, [
            'status' => 'warning',
            'message' => 'The note was not updated.',
        ]);

        return $response->withAddedHeader('Warning', 'The note was not updated.');
    }
}
