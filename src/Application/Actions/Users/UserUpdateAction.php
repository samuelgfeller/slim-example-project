<?php

namespace App\Application\Actions\Users;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use App\Domain\Validation\OutputEscapeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class UserUpdateAction
{
    private Responder $responder;

    protected AuthService $authService;

    protected LoggerInterface $logger;

    protected UserService $userService;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param LoggerFactory $logger
     * @param UserService $userService
     * @param AuthService $authService
     */
    public function __construct(
        Responder $responder,
        LoggerFactory $logger,
        UserService $userService,
        AuthService $authService
    ) {
        $this->responder = $responder;
        $this->authService = $authService;
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('user-update');
        $this->userService = $userService;
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args The routing arguments
     * @return ResponseInterface The response
     * @throws \JsonException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // getUserIdFromToken not transferred to action since it will be session based
        $loggedUserId = (int)$this->getUserIdFromToken($request);

        $userData = $request->getParsedBody();

        $userData['id'] = (int)$args['id'];

        $userRole = $this->authService->getUserRole($loggedUserId);

        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $userData['id'] === $loggedUserId) {

            $user = new User(new ArrayReader($userData));

            try {
                $updated = $this->userService->updateUser($user);
            } catch (ValidationException $exception) {
                return $this->responder->respondWithJsonOnValidationError($exception->getValidationResult(), $response);
            }

            if ($updated) {
                return $this->responder->respondWithJson($response, ['status' => 'success']);
            }
            // If for example values didnt change
            return $this->responder->respondWithJson($response, ['status' => 'warning', 'message' => 'User wasn\'t updated']);
        }
        $this->logger->notice('User ' . $loggedUserId . ' tried to update other user with id: ' . $userData['id']);

        return $this->responder->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'You can only edit your user info or be an admin to view others'],
            403
        );
    }
}
