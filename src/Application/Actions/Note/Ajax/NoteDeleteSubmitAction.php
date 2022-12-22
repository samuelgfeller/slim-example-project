<?php

namespace App\Application\Actions\Note\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Client\Exception\NotAllowedException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Service\NoteDeleter;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpMethodNotAllowedException;

/**
 * Action.
 */
final class NoteDeleteSubmitAction
{
    protected LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param NoteDeleter $noteDeleter
     * @param SessionInterface $session
     * @param LoggerFactory $logger
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly NoteDeleter $noteDeleter,
        private readonly SessionInterface $session,
        LoggerFactory $logger
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('note-delete');
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
            $noteId = (int)$args['note_id'];

            try {
                // Delete note logic
                $deleted = $this->noteDeleter->deleteNote($noteId);

                if ($deleted) {
                    return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
                }

                $response = $this->responder->respondWithJson(
                    $response,
                    ['status' => 'warning', 'message' => 'Note not deleted.']
                );
                $flash = $this->session->getFlash();
                // If not deleted, inform user
                $flash->add('warning', 'The note was not deleted');

                return $response->withAddedHeader('Warning', 'The note was not deleted');
            } catch (NotAllowedException $notAllowedException) {
                // Log event as this should not be able to happen with normal use. User has to manually make exact request
                $this->logger->notice('Action not allowed, user ' . $loggedInUserId . ' tried to delete main note');
                throw new HttpMethodNotAllowedException($request, $notAllowedException->getMessage());
            } catch (ForbiddenException $fe) {
                // Log event as this should not be able to happen with normal use. User has to manually make exact request
                $this->logger->notice(
                    '403 Forbidden, user ' . $loggedInUserId . ' tried to delete other note with id: ' . $noteId
                );
                // Not throwing HttpForbiddenException as it's a json request and response should be json too
                return $this->responder->respondWithJson(
                    $response,
                    ['status' => 'error', 'message' => 'Not allowed to delete note.'],
                    StatusCodeInterface::STATUS_FORBIDDEN
                );
            }
        }

        // UserAuthenticationMiddleware handles redirect to login
        return $response;
    }
}
