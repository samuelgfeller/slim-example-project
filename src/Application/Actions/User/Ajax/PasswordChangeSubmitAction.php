<?php

namespace App\Application\Actions\User\Ajax;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Authentication\Service\PasswordChanger;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\ValidationExceptionOld;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;

/**
 * When user wants to change password being authenticated.
 */
class PasswordChangeSubmitAction
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly Responder $responder,
        private readonly SessionInterface $session,
        private readonly MalformedRequestBodyChecker $malformedRequestBodyChecker,
        private readonly PasswordChanger $passwordChanger,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createLogger('user-service');
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

            try {
                $this->passwordChanger->changeUserPassword(
                    $parsedBody['password'],
                    $parsedBody['password2'],
                    $userId,
                    $parsedBody['old_password'] ?? null,
                );

                return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
            } catch (ValidationExceptionOld $validationException) {
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
}
