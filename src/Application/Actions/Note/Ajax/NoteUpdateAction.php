<?php

namespace App\Application\Actions\Note\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Service\UserRoleFinder;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Data\NoteData;
use App\Domain\Note\Service\NoteFinder;
use App\Domain\Note\Service\NoteUpdater;
use App\Domain\Validation\OutputEscapeService;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * Action.
 */
final class NoteUpdateAction
{
    /**
     * @var Responder
     */
    private Responder $responder;
    protected LoggerInterface $logger;
    protected OutputEscapeService $outputEscapeService;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param SessionInterface $session
     * @param NoteUpdater $noteUpdater
     * @param LoggerFactory $logger
     * @param OutputEscapeService $outputEscapeService
     */
    public function __construct(
        Responder $responder,
        private SessionInterface $session,
        private NoteUpdater $noteUpdater,
        LoggerFactory $logger,
        OutputEscapeService $outputEscapeService,
    ) {
        $this->responder = $responder;
        $this->logger = $logger->addFileHandler('error.log')->createInstance('note-update');
        $this->outputEscapeService = $outputEscapeService;
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
            $noteIdToChange = (int)$args['note_id'];
            $noteValues = $request->getParsedBody();
            // Check that request body syntax is formatted right (if changed, )
            if (null !== $noteValues && [] !== $noteValues && isset($noteValues['message']) && count($noteValues) === 1) {
                try {
                    $updated = $this->noteUpdater->updateNote($noteIdToChange, $noteValues, $loggedInUserId);

                    if ($updated) {
                        return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
                    }
                    $response = $this->responder->respondWithJson($response, [
                        'status' => 'warning',
                        'message' => 'The note was not updated.'
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
                            'message' => 'You can only edit your own note or need to be an admin to edit others'
                        ],
                        403
                    );
                }
            }
            throw new HttpBadRequestException($request, 'Request body malformed.');
        }

        // Not logged in, let AuthenticationMiddleware handle redirect
        return $response;
    }
}
