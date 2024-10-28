<?php

namespace App\Application\Action\Note\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\Note\Exception\InvalidNoteFilterException;
use App\Domain\Note\Service\NoteFilterFinder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class NoteFetchListAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private NoteFilterFinder $noteFilterFinder,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        try {
            // Retrieve notes with given filter values (or none)
            $filteredNotes = $this->noteFilterFinder->findNotesWithFilter($request->getQueryParams());
        } catch (InvalidNoteFilterException $invalidNoteFilterException) {
            return $this->jsonResponder->encodeAndAddToResponse(
                $response,
                // Response format tested in NoteFilterProvider.php
                [
                    'status' => 'error',
                    'message' => $invalidNoteFilterException->getMessage(),
                ],
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        }

        return $this->jsonResponder->encodeAndAddToResponse($response, $filteredNotes);
    }
}
