<?php

namespace App\Application\Actions\Note\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Note\Data\NoteData;
use App\Domain\Note\Service\NoteCreator;
use App\Domain\User\Service\UserFinder;
use App\Domain\Validation\OutputEscapeService;
use App\Infrastructure\User\UserFinderRepository;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * Action.
 */
final class NoteCreateAction
{
    /**
     * @var Responder
     */
    private Responder $responder;
    protected OutputEscapeService $outputEscapeService;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param NoteCreator $noteCreator
     */
    public function __construct(
        Responder $responder,
        private readonly NoteCreator $noteCreator,
        private readonly SessionInterface $session,
        private readonly UserFinder $userFinder,

    ) {
        $this->responder = $responder;
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args
     * @return ResponseInterface The response
     * @throws \JsonException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $noteData = $request->getParsedBody();

            // If a html form name changes, these changes have to be done in the data class constructor
            // Check that request body syntax is formatted right (if changed, )
            if (null !== $noteData && [] !== $noteData && isset($noteData['message'], $noteData['client_id'])
                && count($noteData) === 2) {
                try {
                    $insertId = $this->noteCreator->createNote($noteData, $loggedInUserId);
                } catch (ValidationException $exception) {
                    return $this->responder->respondWithJsonOnValidationError(
                        $exception->getValidationResult(),
                        $response
                    );
                }

                if (0 !== $insertId) {
                    $user = $this->userFinder->findUserById($loggedInUserId);
                    // camelCase according to Google recommendation
                    return $this->responder->respondWithJson($response, [
                        'status' => 'success',
                        'data' => ['userFullName' => $user->firstName . ' ' . $user->surname, 'noteId' => $insertId],
                    ], 201);
                }
                $response = $this->responder->respondWithJson($response, [
                    'status' => 'warning',
                    'message' => 'Note not created'
                ]);
                return $response->withAddedHeader('Warning', 'The note could not be created');
            }
            throw new HttpBadRequestException($request, 'Request body malformed.');
        }

        // Handled by AuthenticationMiddleware
        return $response;
    }
}
