<?php

namespace App\Application\Actions\Note\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Authorization\UnauthorizedException;
use App\Domain\Note\Exception\InvalidNoteFilterException;
use App\Domain\Note\Service\NoteFilterFinder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * List notes linked to user action.
 */
final class NoteListFetchAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param NoteFilterFinder $noteFilterFinder
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly NoteFilterFinder $noteFilterFinder,
    ) {
    }

    /**
     * Note list all and own Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @throws \JsonException
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
        } // If user requests its own notes he has to be logged in
        catch (UnauthorizedException $unauthorizedException) {
            // Respond with status code 401 Unauthorized which is caught in the Ajax call
            return $this->responder->respondWithJson(
                $response,
                [
                    'loginUrl' => $this->responder->urlFor(
                        'login-page',
                        [],
                        ['redirect' => $this->responder->urlFor('client-list-assigned-to-me-page')]
                    ),
                ],
                401
            );
        }

        return $this->responder->respondWithJson($response, $filteredNotes);
    }
}
