<?php

namespace App\Controllers\Posts;

use App\Application\Controllers\Controller;
use App\Domain\Post\PostRepositoryInterface;
use App\Domain\Post\PostService;
use App\Domain\Post\PostValidation;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Handlers\Strategies\RequestHandler;
use Firebase\JWT\JWT;

class PostController extends Controller {

    protected $postService;
    protected $postValidation;

    public function __construct(LoggerInterface $logger, PostService $postService, PostValidation $postValidation) {
        parent::__construct($logger);
        $this->postService = $postService;
        $this->postValidation = $postValidation;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $post = $this->postService->findPost($id);
//        var_dump($this->container->get('logger'));
//        $response->getBody()->write('GET request');

        $this->logger->info('posts/' . $id . ' has been called');
//        var_dump($this->logger);
        return $this->respondWithJson($response, $post);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
//        var_dump($request->getParsedBody());
    
        $data = $request->getParsedBody();
        
        // todo validation
    
        $name = $data['name'];
        $email = $data['email'];
//        var_dump($data);
        $updated = $this->postService->updatePost($id,$name,$email);
        if ($updated) {
            return $this->respondWithJson($response, ['success' => true]);
        }
        return $this->respondWithJson($response, ['success' => false]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $postId = $args['id'];
/* https://github.com/D4rkMindz/roast.li/blob/master/src/Controller/UserController.php
$validationResult = $this->postValidation->validateDeletion($postId, $this->getPostId());
        if ($validationResult->fails()) {
            $responseData = [
                'success' => false,
                'validation' => $validationResult->toArray(),
            ];
            return $this->respondWithJson($response, $responseData, 422);
        }*/
        $deleted = $this->postService->deletePost($postId);
        if ($deleted) {
            return $this->respondWithJson($response, ['success' => true]);
        }
        return $this->respondWithJson($response, ['success' => false]);
    }

    public function create(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        if(null !== $data) {
            $postData = [
                'email' => $data['email'],
                'password1' => $data['password1'],
                'password2' => $data['password2'],
            ];

            $validationResult = $this->postValidation->validatePostRegistration($postData);
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

    public function list(Request $request, Response $response, array $args) {
        $allPosts = $this->postService->findAllPosts();
        //somehow that doesnt work
//        $this->respondWithData($response, va$allPosts);
        //    $this->respondWithDataPrettyJson($response, $allPosts);

        // This works though
//         $response->getBody()->write(json_encode($allPosts));
//         $response->getBody()->write('omg');
        $response->withHeader('Content-Type', 'application/json');
        return $this->respondWithJson($response, $allPosts);

    }
}
