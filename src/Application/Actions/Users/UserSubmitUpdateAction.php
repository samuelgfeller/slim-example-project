<?php

namespace App\Application\Actions\Users;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\ForbiddenException;
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
        $this->logger = $logger->addFileHandler('error.log')->createInstance('user-update');
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
        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $userIdToChange = (int)$args['user_id'];
            $userValuesToChange = $request->getParsedBody();

            try {
                $updated = $this->userService->updateUser($userIdToChange, $userValuesToChange, $loggedInUserId);
            } catch (ValidationException $exception) {
                return $this->responder->respondWithJsonOnValidationError(
                    $exception->getValidationResult(),
                    $response
                );
            } catch (ForbiddenException $fe){
                return $this->responder->respondWithJson(
                    $response,
                    ['status' => 'error', 'message' => 'You can only edit your user info or be an admin to edit others'],
                    403
                );
            }

            if ($updated) {
                return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
            }
            // If for example values didnt change
            return $this->responder->respondWithJson(
                $response,
                ['status' => 'warning', 'message' => 'User wasn\'t updated']
            );
        }
        // Status 401 when not authenticated and 403 when not allowed (logged in but missing right)
        return $this->responder->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'Please login to make the changes.'],
            401
        );
    }
}
