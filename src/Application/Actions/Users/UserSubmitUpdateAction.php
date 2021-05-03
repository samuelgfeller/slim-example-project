<?php

namespace App\Application\Actions\Users;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\UserService;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class UserSubmitUpdateAction
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
     * @param SessionInterface $session
     */
    public function __construct(
        Responder $responder,
        LoggerFactory $logger,
        UserService $userService,
        AuthService $authService,
        private SessionInterface $session

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
        if (($loggedInUserId = $this->session->get('user_id')) !== null){

            $userIdToChange = (int)$args['user_id'];
            $userValuesToChange = $request->getParsedBody();

            $userRole = $this->authService->getUserRoleById($loggedInUserId);

            // Check if it's admin or if it's its own user
            if ($userRole === 'admin' || $userIdToChange === $loggedInUserId) {
                try {
                    $updated = $this->userService->updateUser($loggedInUserId, $userValuesToChange);
                } catch (ValidationException $exception) {
                    return $this->responder->respondWithJsonOnValidationError($exception->getValidationResult(), $response);
                }

                if ($updated) {
                    return $this->responder->respondWithJson($response, ['status' => 'success']);
                }
                // If for example values didnt change
                return $this->responder->respondWithJson($response, ['status' => 'warning', 'message' => 'User wasn\'t updated']);
            }
            $this->logger->notice('User ' . $loggedInUserId . ' tried to update other user with id: ' . $userIdToChange);

            return $this->responder->respondWithJson(
                $response,
                ['status' => 'error', 'message' => 'You can only edit your user info or be an admin to edit others'],
                403
            );
        }
        return $this->responder->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'Please login to make the changes'],
            403
        );

    }
}
