<?php

namespace App\Application\Controllers\Posts;

use App\Application\Controllers\Controller;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Post\Post;
use App\Domain\Post\PostService;
use App\Domain\Post\PostValidation;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use App\Domain\Validation\OutputEscapeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Handlers\Strategies\RequestHandler;

class PostController extends Controller
{

    protected PostService $postService;
    protected UserService $userService;
    protected OutputEscapeService $outputEscapeService;
    protected AuthService $authService;
    protected LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        PostService $postService,
        UserService $userService,
        AuthService $authService,
        OutputEscapeService $outputEscapeService
    ) {
        parent::__construct($logger);
        $this->postService = $postService;
        $this->userService = $userService;
        $this->authService = $authService;
        $this->outputEscapeService = $outputEscapeService;
    }

    public function get(Request $request, Response $response, array $args): Response
    {

    }

    public function list(Request $request, Response $response, array $args)
    {


    }

    // used
    public function getOwnPosts(Request $request, Response $response, array $args): Response
    {

    }

    public function update(Request $request, Response $response, array $args): Response
    {

    }

    public function delete(Request $request, Response $response, array $args): Response
    {

    }

    public function create(Request $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (int)$this->getUserIdFromToken($request);

        if (null !== $postData = $request->getParsedBody()) {

            $post = new Post(new ArrayReader($postData));
            $post->setUserId($userId);

            try {
                $insertId = $this->postService->createPost($post);
            } catch (ValidationException $exception) {
                return $this->respondValidationError($exception->getValidationResult(), $response);
            }

            if (null !== $insertId) {
                return $this->respondWithJson($response, ['status' => 'success'], 201);
            }
            $response = $this->respondWithJson($response, ['status' => 'warning', 'message' => 'Post not created']);
            return $response->withAddedHeader('Warning', 'The post could not be created');
        }
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'Request body empty']);
    }
}
