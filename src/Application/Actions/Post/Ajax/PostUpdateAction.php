<?php

namespace App\Application\Actions\Post\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Service\UserRoleFinder;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Post\Data\PostData;
use App\Domain\Post\Service\PostFinder;
use App\Domain\Post\Service\PostUpdater;
use App\Domain\Validation\OutputEscapeService;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Action.
 */
final class PostUpdateAction
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
     * @param PostUpdater $postUpdater
     * @param LoggerFactory $logger
     * @param OutputEscapeService $outputEscapeService
     */
    public function __construct(
        Responder $responder,
        private SessionInterface $session,
        private PostUpdater $postUpdater,
        LoggerFactory $logger,
        OutputEscapeService $outputEscapeService,
    ) {
        $this->responder = $responder;
        $this->logger = $logger->addFileHandler('error.log')->createInstance('post-update');
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
            $postIdToChange = (int)$args['post_id'];
            $postValues = $request->getParsedBody();

            try {
                $updated = $this->postUpdater->updatePost($postIdToChange, $postValues, $loggedInUserId);

                if ($updated) {
                    return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
                }
                $response = $this->responder->respondWithJson($response, [
                    'status' => 'warning',
                    'message' => 'The post was not updated'
                ]);
                return $response->withAddedHeader('Warning', 'The post was not updated');
            } catch (ValidationException $exception) {
                return $this->responder->respondWithJsonOnValidationError(
                    $exception->getValidationResult(),
                    $response
                );
            } catch (ForbiddenException $fe) {
                return $this->responder->respondWithJson(
                    $response,
                    ['status' => 'error', 'message' => 'You can only edit your own post or be an admin to edit others'],
                    403
                );
            }
        }

        // Not logged in, let AuthenticationMiddleware handle redirect
        return $response;
    }
}
