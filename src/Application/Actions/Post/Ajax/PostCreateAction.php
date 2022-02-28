<?php

namespace App\Application\Actions\Post\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Post\Data\PostData;
use App\Domain\Post\Service\PostCreator;
use App\Domain\Validation\OutputEscapeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class PostCreateAction
{
    /**
     * @var Responder
     */
    private Responder $responder;
    protected OutputEscapeService $outputEscapeService;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param PostCreator $postCreator
     */
    public function __construct(
        Responder $responder,
        private PostCreator $postCreator
    ) {
        $this->responder = $responder;
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

        if (null !== $postData = $request->getParsedBody()) {

            $post = new PostData($postData);
            $post->userId = $userId;

            try {
                $insertId = $this->postCreator->createPost($post);
            } catch (ValidationException $exception) {
                return $this->responder->respondWithJsonOnValidationError($exception->getValidationResult(), $response);
            }

            if (null !== $insertId) {
                return $this->responder->respondWithJson($response, ['status' => 'success'], 201);
            }
            $response = $this->responder->respondWithJson($response, ['status' => 'warning', 'message' => 'Post not created']);
            return $response->withAddedHeader('Warning', 'The post could not be created');
        }
        return $this->responder->respondWithJson($response, ['status' => 'error', 'message' => 'Request body empty']);
    }
}
