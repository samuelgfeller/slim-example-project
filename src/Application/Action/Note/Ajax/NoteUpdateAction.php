<?php

namespace App\Application\Action\Note\Ajax;

use App\Application\Renderer\JsonEncoder;
use App\Domain\Note\Service\NoteUpdater;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class NoteUpdateAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private NoteUpdater $noteUpdater,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $noteIdToChange = (int)$args['note_id'];
        $noteValues = (array)$request->getParsedBody();

        $updated = $this->noteUpdater->updateNote($noteIdToChange, $noteValues);

        if ($updated) {
            return $this->jsonEncoder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null]);
        }
        $response = $this->jsonEncoder->encodeAndAddToResponse($response, [
            'status' => 'warning',
            'message' => 'The note was not updated.',
        ]);

        return $response->withAddedHeader('Warning', 'The note was not updated.');
    }
}
