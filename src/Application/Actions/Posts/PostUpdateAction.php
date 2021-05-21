<?php

namespace App\Application\Actions\Posts;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Service\UserRoleFinder;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Post\DTO\Post;
use App\Domain\Post\Service\PostFinder;
use App\Domain\Post\Service\PostUpdater;
use App\Domain\Validation\OutputEscapeService;
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
     * @param PostFinder $postFinder
     * @param PostUpdater $postUpdater
     * @param LoggerFactory $logger
     * @param OutputEscapeService $outputEscapeService
     * @param UserRoleFinder $userRoleFinder
     */
    public function __construct(
        Responder $responder,
        private PostFinder $postFinder,
        private PostUpdater $postUpdater,
        LoggerFactory $logger,
        OutputEscapeService $outputEscapeService,
        private UserRoleFinder $userRoleFinder
    ) {
        $this->responder = $responder;
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('post-update');
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
        $userId = (int)$this->getUserIdFromToken($request);

        $id = (int)$args['id'];

        $postFromDb = $this->postFinder->findPost($id);

        // I write the role logic always for each function and not a general service "isAuthorised" function because it's too different every time
        $userRole = $this->userRoleFinder->getUserRoleById($userId);
        // Check if it's admin or if it's its own post
        if ($userRole === 'admin' || $postFromDb->userId === $userId) {
            // todo check if parsedbody is empty everywhere
            if (null !== $postData = $request->getParsedBody()) {
                // todo maybe add mapping a layer between client body and application logic

                $post = new Post($postData);
                // Needed to tell repo what data to update
                $post->id = $postFromDb['id'];

                try {
                    $updated = $this->postUpdater->updatePost($post);
                } catch (ValidationException $exception) {
                    return $this->responder->respondWithJsonOnValidationError(
                        $exception->getValidationResult(),
                        $response
                    );
                }

                if ($updated) {
                    return $this->responder->respondWithJson($response, ['status' => 'success']);
                }
                $response = $this->responder->respondWithJson(
                    $response,
                    ['status' => 'warning', 'message' => 'The post was not updated']
                );
                return $response->withAddedHeader('Warning', 'The post was not updated');
            }
            $response = $this->responder->respondWithJson(
                $response,
                ['status' => 'error', 'message' => 'Request body empty'],
                400
            );
            return $response->withAddedHeader('Warning', '');
        }
        $this->logger->notice('User ' . $userId . ' tried to update other post with id: ' . $id);
        return $this->responder->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'You have to be admin or post creator to update this post'],
            403
        );
    }
}
