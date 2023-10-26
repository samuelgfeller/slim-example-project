<?php

namespace App\Application\Actions\Note\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Note\Exception\InvalidNoteFilterException;
use App\Domain\Note\Service\NoteFilterFinder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class NoteFetchListAction
{
    public function __construct(
        private readonly Responder $responder,
        private readonly NoteFilterFinder $noteFilterFinder,
    ) {
    }

    /**
     * Fetch note list with given params action.
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
        try {
            // Retrieve notes with given filter values (or none)
            $filteredNotes = $this->noteFilterFinder->findNotesWithFilter($request->getQueryParams());
        } catch (InvalidNoteFilterException $invalidNoteFilterException) {
            return $this->responder->respondWithJson(
                $response,
                // Response format tested in NoteFilterProvider.php
                [
                    'status' => 'error',
                    'message' => $invalidNoteFilterException->getMessage(),
                ],
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        }

        return $this->responder->respondWithJson($response, $filteredNotes);
    }
}
