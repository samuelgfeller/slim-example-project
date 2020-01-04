<?php

namespace App\Controllers\Posts;

use App\Application\Controllers\Controller;
use App\Domain\Post\PostRepositoryInterface;
use App\Domain\Post\PostService;
use App\Domain\Post\PostValidation;
use App\Domain\User\UserService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Handlers\Strategies\RequestHandler;
use Firebase\JWT\JWT;

class PostController extends Controller
{

    protected $postService;
    protected $postValidation;
    protected $userService;

    public function __construct(
        LoggerInterface $logger,
        PostService $postService,
        PostValidation $postValidation,
        UserService $userService
    ) {
        parent::__construct($logger);
        $this->postService = $postService;
        $this->postValidation = $postValidation;
        $this->userService = $userService;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $post = $this->postService->findPost($id);

        // Get user information connected to post
        $user = $this->userService->findUser($post['user_id']);

        // Add user name info to post
        $postWithUser = $post;
        $postWithUser['user_name'] = $user['name'];
        return $this->respondWithJson($response, $postWithUser);
    }

    public function list(Request $request, Response $response, array $args)
    {
        $allPosts = $this->postService->findAllPosts();
        // todo output escaping
        // Add user name info to post
        $postsWithUser = [];
        // todo move this logic to service
        foreach ($allPosts as $post) {
            // Get user information connected to post
            $user = $this->userService->findUser($post['user_id']);
            $post['user_name'] = $user['name'];
            $postsWithUser[] = $post;
        }

        return $this->respondWithJson($response, $postsWithUser);

    }

    public function getOwnPosts(Request $request, Response $response, array $args): Response
    {
        // option 1 /posts?user=xxx and then $request->getQueryParams('user'); but that would mean that the user has to know its id
        // option 2 /own-posts and get user id from token data body

        $userId = $this->getUserIdFromToken($request);

        $posts = $this->postService->findAllPostsFromUser($userId);

        return $this->respondWithJson($response, $posts);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        // todo check if user has authorisation with token data
        $userId = $this->getUserIdFromToken($request);

        $id = (int)$args['id'];

        $post = $this->postService->findPost($id);
        // I write the role logic always for each function and not a general service "isAuthorised" function because it's too different every time
        $userRole = $this->userService->getUserRole($userId);

        // Check if it's admin or if it's its own post
        if ($userRole === 'admin' || (int) $post['user_id'] === $userId) {

//      var_dump($request->getParsedBody());

            $data = $request->getParsedBody();

            $postData = [
                'message' => htmlspecialchars($data['message']),
                'user' => htmlspecialchars($data['user']),
            ];

            $validationResult = $this->postValidation->validatePostCreation($postData);
            if ($validationResult->fails()) {
                $responseData = [
                    'success' => false,
                    'validation' => $validationResult->toArray(),
                ];

                return $this->respondWithJson($response, $responseData, 422);
            }
//        var_dump($data);
            $updated = $this->postService->updatePost($id, $postData);
            if ($updated) {
                return $this->respondWithJson($response, ['status' => 'success']);
            }
            $response = $this->respondWithJson($response, ['status' => 'warning', 'message' => 'The post was not updated']);
            return $response->withAddedHeader('Warning', 'The post was not updated');
        }
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'You have to be admin or post creator to update this post'], 401);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        // todo check if user has authorisation
        $postId = $args['id'];


        $deleted = $this->postService->deletePost($postId);
        if ($deleted) {
            return $this->respondWithJson($response, ['success' => true]);
        }
        return $this->respondWithJson($response, ['success' => false]);
    }

    public function create(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (null !== $data) {
            $postData = [
                'message' => htmlspecialchars($data['message']),
                'user' => htmlspecialchars($data['user']),
            ];

            $validationResult = $this->postValidation->validatePostCreation($postData);
            if ($validationResult->fails()) {
                $responseData = [
                    'success' => false,
                    'validation' => $validationResult->toArray(),
                ];

                return $this->respondWithJson($response, $responseData, 422);
            }
            $insertId = $this->postService->createPost($postData);

            if (null !== $insertId) {
                return $this->respondWithJson($response, ['success' => true]);
            }
            return $this->respondWithJson($response, ['success' => false, 'message' => 'Post could not be inserted']);
        }
        return $this->respondWithJson($response, ['success' => false, 'message' => 'Request body empty']);
    }


}
