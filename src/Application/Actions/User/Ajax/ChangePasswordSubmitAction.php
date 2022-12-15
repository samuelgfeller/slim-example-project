<?php

namespace App\Application\Actions\User\Ajax;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Authentication\Service\PasswordChanger;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\ValidationException;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * When user wants to change password being authenticated.
 */
class ChangePasswordSubmitAction
{
    private LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder
     * @param SessionInterface $session
     * @param MalformedRequestBodyChecker $malformedRequestBodyChecker
     * @param PasswordChanger $passwordChanger
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly SessionInterface $session,
        private readonly MalformedRequestBodyChecker $malformedRequestBodyChecker,
        private readonly PasswordChanger $passwordChanger,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('user-service');
    }

    /**
     * Check if token is valid and if yes display password form.
     *
     * @param ServerRequest $request
     * @param Response $response
     * @param array $args
     *
     * @throws \JsonException
     *
     * @return Response
     */
    public function __invoke(ServerRequest $request, Response $response, array $args): Response
    {
        $parsedBody = $request->getParsedBody();
        $userId = $args['user_id'];
        $flash = $this->session->getFlash();

        if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys($parsedBody, [
            'password',
            'password2',
        ], ['old_password'])) {
            try {
                $this->passwordChanger->changeUserPassword(
                    $parsedBody['password'],
                    $parsedBody['password2'],
                    $userId,
                    $parsedBody['old_password'] ?? null,
                );

                return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
            } catch (ValidationException $validationException) {
                return $this->responder->respondWithJsonOnValidationError(
                    $validationException->getValidationResult(),
                    $response,
                );
            } catch (ForbiddenException $forbiddenException) {
                // Not throwing HttpForbiddenException as it's a json request and response should be json too
                return $this->responder->respondWithJson(
                    $response,
                    ['status' => 'error', 'message' => 'Not allowed to change password.'],
                    StatusCodeInterface::STATUS_FORBIDDEN
                );
            }
        }

        $flash->add('error', 'There is something wrong with the application.');
        // Prevent to log passwords
        $this->logger->error(
            'Password change request malformed. Array keys: ' . json_encode(
                array_keys($parsedBody ?? []),
                JSON_THROW_ON_ERROR
            )
        );
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        throw new HttpBadRequestException($request, 'Request body malformed.');
    }
}
