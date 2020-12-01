<?php

namespace App\Application\Actions\Users;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\User\UserService;
use App\Domain\Validation\OutputEscapeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Action.
 */
final class UserViewAction
{
    private Responder $responder;

    protected AuthService $authService;

    protected LoggerInterface $logger;

    protected UserService $userService;

    // Needed only if request is ajax and doest go through twig
    protected OutputEscapeService $outputEscapeService;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param LoggerInterface $logger
     * @param UserService $userService
     * @param AuthService $authService
     * @param OutputEscapeService $outputEscapeService
     */
    public function __construct(
        Responder $responder,
        LoggerInterface $logger,
        UserService $userService,
        AuthService $authService,
        OutputEscapeService $outputEscapeService

    ) {
        $this->responder = $responder;
        $this->authService = $authService;
        $this->logger = $logger;
        $this->userService = $userService;
        $this->outputEscapeService = $outputEscapeService;
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

        $id = (int)$args['id'];

        $userRole = $this->authService->getUserRole($loggedUserId);

        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $id === $loggedUserId) {
            $user = $this->userService->findUser($id);
            $user = $this->outputEscapeService->escapeOneDimensionalArray($user);
            return $this->responder->respondWithJson($response, $user);
        }
        $this->logger->notice('User ' . $loggedUserId . ' tried to view other user with id: ' . $id);

        return $this->responder->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'You can only view your user info or be an admin to view others'],
            403
        );
    }
}
