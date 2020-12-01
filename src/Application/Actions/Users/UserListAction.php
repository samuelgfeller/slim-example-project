<?php

namespace App\Application\Actions\Users;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\User\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Action.
 */
final class UserListAction
{
    private Responder $responder;

    protected AuthService $authService;

    protected LoggerInterface $logger;

    protected UserService $userService;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param LoggerInterface $logger
     * @param UserService $userService
     * @param AuthService $authService
     */
    public function __construct(Responder $responder,
        LoggerInterface $logger,
        UserService $userService,
        AuthService $authService)
    {
        $this->responder = $responder;
        $this->authService = $authService;
        $this->logger = $logger;
        $this->userService = $userService;
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // getUserIdFromToken not transferred to action since it will be session based
        $loggedUserId = (int)$this->getUserIdFromToken($request);

        $userRole = $this->authService->getUserRole($loggedUserId);

        if ($userRole === 'admin') {
            $allUsers = $this->userService->findAllUsers();

            // Twig escapes automatically but if the data comes from ajax then it should be manually
            $allUsers = $this->outputEscapeService->escapeTwoDimensionalArray($allUsers);

            $response->withHeader('Content-Type', 'application/json');
            return $this->responder->respondWithJson($response, $allUsers);
        }
        $this->logger->notice('User ' . $loggedUserId . ' tried to view all other users');

        return $this->responder->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'You have to be admin to view all users'],
            403
        );    }
}
