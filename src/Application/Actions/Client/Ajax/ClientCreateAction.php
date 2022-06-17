<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Client\Service\ClientCreator;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Validation\OutputEscapeService;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * Action.
 */
final class ClientCreateAction
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
     * @param ClientCreator $clientCreator
     */
    public function __construct(
        Responder                $responder,
        private readonly ClientCreator    $clientCreator,
        private readonly SessionInterface $session,
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
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $postData = $request->getParsedBody();

            // If a html form name changes, these changes have to be done in the data class constructor
            // Check that request body syntax is formatted right (if changed, )
            if (null !== $postData && [] !== $postData && isset($postData['message']) && count($postData) === 1) {
                try {
                    $insertId = $this->clientCreator->createPost($postData, $loggedInUserId);
                } catch (ValidationException $exception) {
                    return $this->responder->respondWithJsonOnValidationError(
                        $exception->getValidationResult(),
                        $response
                    );
                }

                if (0 !== $insertId) {
                    return $this->responder->respondWithJson($response, ['status' => 'success'], 201);
                }
                $response = $this->responder->respondWithJson($response, [
                    'status' => 'warning',
                    'message' => 'Post not created'
                ]);
                return $response->withAddedHeader('Warning', 'The post could not be created');
            }
            throw new HttpBadRequestException($request, 'Request body malformed.');
        }

        // Handled by AuthenticationMiddleware
        return $response;
    }
}
