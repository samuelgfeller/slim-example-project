<?php

namespace App\Module\Note\Action\Ajax;

use App\Core\Application\Responder\JsonResponder;
use App\Module\Note\Domain\Exception\InvalidNoteFilterException;
use App\Module\Note\Domain\Service\NoteFilterFinder;
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
