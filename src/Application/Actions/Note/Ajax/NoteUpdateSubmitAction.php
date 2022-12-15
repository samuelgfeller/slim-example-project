<?php

namespace App\Application\Actions\Note\Ajax;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Service\NoteUpdater;
use App\Domain\Validation\ValidationException;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * Action.
 */
final class NoteUpdateSubmitAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param SessionInterface $session
     * @param NoteUpdater $noteUpdater
     * @param LoggerFactory $logger
     * @param MalformedRequestBodyChecker $malformedRequestBodyChecker
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly SessionInterface $session,
        private readonly NoteUpdater $noteUpdater,
        LoggerFactory $logger,
        private readonly MalformedRequestBodyChecker $malformedRequestBodyChecker
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('note-update');
    }

    /**
     * Action.
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
        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $noteIdToChange = (int)$args['note_id'];
            $noteValues = $request->getParsedBody();
            // Check that request body syntax is formatted right (if changed, )
            if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys(
                $noteValues,
                [],
                ['message', 'is_main', 'hidden']
            )) {
                try {
                    $updated = $this->noteUpdater->updateNote($noteIdToChange, $noteValues);

                    if ($updated) {
                        return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
                    }
                    $response = $this->responder->respondWithJson($response, [
                        'status' => 'warning',
                        'message' => 'The note was not updated.',
                    ]);

                    return $response->withAddedHeader('Warning', 'The note was not updated.');
                } catch (ValidationException $exception) {
                    return $this->responder->respondWithJsonOnValidationError(
                        $exception->getValidationResult(),
                        $response
                    );
                } catch (ForbiddenException $fe) {
                    return $this->responder->respondWithJson(
                        $response,
                        [
                            // Response content asserted in ClientReadCaseProvider.php
                            'status' => 'error',
                            'message' => 'Not allowed to change note.',
                        ],
                        StatusCodeInterface::STATUS_FORBIDDEN
                    );
                }
            }
            throw new HttpBadRequestException($request, 'Request body malformed.');
        }

        // Not logged in, let AuthenticationMiddleware handle redirect
        return $response;
    }
}
