<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Post\Service\ClientDeleter;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Action.
 */
final class ClientDeleteAction
{
    /**
     * @var Responder
     */
    private Responder $responder;
    protected LoggerInterface $logger;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ClientDeleter $postDeleter
     * @param SessionInterface $session
     * @param LoggerFactory $logger
     */
    public function __construct(
        Responder                $responder,
        private ClientDeleter    $postDeleter,
        private SessionInterface $session,
        LoggerFactory            $logger

    ) {
        $this->responder = $responder;
        $this->logger = $logger->addFileHandler('error.log')->createInstance('post-delete');
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
            $postId = (int)$args['post_id'];

            try {
                // Delete post logic
                $deleted = $this->postDeleter->deletePost($postId, $loggedInUserId);

                if ($deleted) {
                    return $this->responder->respondWithJson($response, ['status' => 'success']);
                }

                $response = $this->responder->respondWithJson(
                    $response,
                    ['status' => 'warning', 'message' => 'Post not deleted.']
                );
                $flash = $this->session->getFlash();
                // If not deleted, inform user
                $flash->add('warning', 'The post was not deleted');
                return $response->withAddedHeader('Warning', 'The post was not deleted');
            } catch (ForbiddenException $fe) {
                // Log event as this should not be able to happen with normal use. User has to manually make exact request
                $this->logger->notice(
                    '403 Forbidden, user ' . $loggedInUserId . ' tried to delete other post with id: ' . $postId
                );
                // Not throwing HttpForbiddenException as it's a json request and response should be json too
                return $this->responder->respondWithJson(
                    $response,
                    ['status' => 'error', 'message' => 'You have to be admin or post creator to update this post'],
                    403
                );
            }
        }

        // UserAuthenticationMiddleware handles redirect to login
        return $response;
    }
}
