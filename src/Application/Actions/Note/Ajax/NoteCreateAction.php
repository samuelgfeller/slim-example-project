<?php

namespace App\Application\Actions\Note\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Note\Service\NoteCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class NoteCreateAction
{
    public function __construct(
        private readonly Responder $responder,
        private readonly NoteCreator $noteCreator,
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
        $noteValues = $request->getParsedBody();

        // To domain function to validate and create note
        $noteCreationData = $this->noteCreator->createNote($noteValues);

        if (0 !== $noteCreationData['note_id']) {
            // camelCase according to Google recommendation
            return $this->responder->respondWithJson($response, [
                'status' => 'success',
                'data' => [
                    'userFullName' => $noteCreationData['user_full_name'],
                    'noteId' => $noteCreationData['note_id'],
                    'createdDateFormatted' => $noteCreationData['formatted_creation_timestamp'],
                ],
            ], 201);
        }
        $response = $this->responder->respondWithJson($response, [
            'status' => 'warning',
            'message' => 'Note not created',
        ]);

        return $response->withAddedHeader('Warning', 'The note could not be created');
    }
}
