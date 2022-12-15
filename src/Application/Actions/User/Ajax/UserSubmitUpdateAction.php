<?php

namespace App\Application\Actions\User\Ajax;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Service\UserUpdater;
use App\Domain\Validation\ValidationException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

final class UserSubmitUpdateAction
{
    private LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param UserUpdater $userUpdater
     * @param MalformedRequestBodyChecker $malformedRequestBodyChecker
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly UserUpdater $userUpdater,
        private readonly MalformedRequestBodyChecker $malformedRequestBodyChecker,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('user-update-action');
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args The routing arguments
     *
     * @throws \JsonException
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // Id in url user_id defined in routes.php
        $userIdToChange = (int)$args['user_id'];
        $userValuesToChange = $request->getParsedBody();
        if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys($userValuesToChange, [], [
            'first_name',
            'surname',
            'email',
            'status',
            'user_role_id',
        ])) {
            try {
                $updated = $this->userUpdater->updateUser($userIdToChange, $userValuesToChange);
            } catch (ValidationException $exception) {
                return $this->responder->respondWithJsonOnValidationError(
                    $exception->getValidationResult(),
                    $response
                );
            } catch (ForbiddenException $fe) {
                return $this->responder->respondWithJson(
                    $response,
                    [
                        'status' => 'error',
                        'message' => 'Not allowed to update user.',
                    ],
                    StatusCodeInterface::STATUS_FORBIDDEN
                );
            }

            if ($updated) {
                return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
            }
            // If for example values didn't change
            return $this->responder->respondWithJson(
                $response,
                ['status' => 'warning', 'message' => 'User wasn\'t updated']
            );
        }

        // Prevent to log passwords
        $this->logger->error(
            'Password change request malformed. Array keys: ' . json_encode(
                array_keys($userValuesToChange ?? []),
                JSON_THROW_ON_ERROR
            )
        );
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        throw new HttpBadRequestException($request, 'Request body malformed.');
    }
}
