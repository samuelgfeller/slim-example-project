<?php

namespace App\Application\Actions\Posts;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Post\Post;
use App\Domain\Post\PostService;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use App\Domain\Validation\OutputEscapeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class PostUpdateAction
{
    /**
     * @var Responder
     */
    private Responder $responder;
    protected PostService $postService;
    protected UserService $userService;
    protected OutputEscapeService $outputEscapeService;
    protected AuthService $authService;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param PostService $postService
     * @param UserService $userService
     * @param OutputEscapeService $outputEscapeService
     * @param AuthService $authService
     */
    public function __construct(
        Responder $responder,
        PostService $postService,
        UserService $userService,
        OutputEscapeService $outputEscapeService,
        AuthService $authService
    ) {
        $this->responder = $responder;
        $this->postService = $postService;
        $this->userService = $userService;
        $this->outputEscapeService = $outputEscapeService;
        $this->authService = $authService;
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
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (int)$this->getUserIdFromToken($request);

        $id = (int)$args['id'];

        $postFromDb = $this->postService->findPost($id);

        // I write the role logic always for each function and not a general service "isAuthorised" function because it's too different every time
        $userRole = $this->authService->getUserRole($userId);
        // Check if it's admin or if it's its own post
        if ($userRole === 'admin' || (int)$postFromDb['user_id'] === $userId) {

            // todo check if parsedbody is empty everywhere
            if (null !== $postData = $request->getParsedBody()) {
                // todo maybe add mapping a layer between client body and application logic

                $post = new Post(new ArrayReader($postData));
                // Needed to tell repo what data to update
                $post->setId($postFromDb['id']);

                try {
                    $updated = $this->postService->updatePost($post);
                } catch (ValidationException $exception) {
                    return $this->responder->respondWithJsonOnValidationError($exception->getValidationResult(), $response);
                }

                if ($updated) {
                    return $this->responder->respondWithJson($response, ['status' => 'success']);
                }
                $response = $this->responder->respondWithJson($response,
                                                   ['status' => 'warning', 'message' => 'The post was not updated']);
                return $response->withAddedHeader('Warning', 'The post was not updated');
            }
            $response = $this->responder->respondWithJson($response, ['status' => 'error', 'message' => 'Request body empty'],
                                               400);
            return $response->withAddedHeader('Warning', '');
        }
        $this->logger->notice('User ' . $userId . ' tried to update other post with id: ' . $id);
        return $this->responder->respondWithJson($response,
                                      ['status' => 'error', 'message' => 'You have to be admin or post creator to update this post'], 403);
    }
}
