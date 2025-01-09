<?php

namespace App\Module\Note\Create\Action;

use App\Core\Application\Responder\JsonResponder;
use App\Module\Note\Create\Service\NoteCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class NoteCreateAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private NoteCreator $noteCreator,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $noteValues = (array)$request->getParsedBody();

        // To domain function to validate and create note
        $noteCreationData = $this->noteCreator->createNote($noteValues);

        if (0 !== $noteCreationData['note_id']) {
            // camelCase according to Google recommendation
            return $this->jsonResponder->encodeAndAddToResponse($response, [
                'status' => 'success',
                'data' => [
                    'userFullName' => $noteCreationData['user_full_name'],
                    'noteId' => $noteCreationData['note_id'],
                    'createdDateFormatted' => $noteCreationData['formatted_creation_timestamp'],
                ],
            ], 201);
        }
        $response = $this->jsonResponder->encodeAndAddToResponse($response, [
            'status' => 'warning',
            'message' => 'Note not created',
        ]);

        return $response->withAddedHeader('Warning', 'The note could not be created');
    }
}
