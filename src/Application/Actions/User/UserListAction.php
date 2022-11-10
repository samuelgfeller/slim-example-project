<?php

namespace App\Application\Actions\User;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Service\UserRoleFinder;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Service\UserFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Action.
 */
final class UserListAction
{
    protected LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param LoggerFactory $logger
     * @param UserFinder $userFinder
     * @param UserRoleFinder $userRoleFinder
     */
    public function __construct(
        private readonly Responder $responder,
        LoggerFactory $logger,
        private UserFinder $userFinder,
        private UserRoleFinder $userRoleFinder,
    ) {
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('user-list');
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
        return $this->responder->render($response, 'hello/hello.html.php');
        // getUserIdFromToken not transferred to action since it will be session based
        $loggedUserId = (int)$this->getUserIdFromToken($request);

        $userRole = $this->userRoleFinder->getUserRoleById($loggedUserId);

        if ($userRole === 'admin') {
            $allUsers = $this->userFinder->findAllUsersResultDataForList();

            // Output has to be escaped since PHP-View doesn't have a protection against XSS-attacks
//            $allUsers = $this->outputEscapeService->escapeTwoDimensionalArray($allUsers);

            $response->withHeader('Content-Type', 'application/json');
            return $this->responder->respondWithJson($response, $allUsers);
        }
        $this->logger->notice('User ' . $loggedUserId . ' tried to view all other users');

        return $this->responder->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'You have to be admin to view all users'],
            403
        );
    }
}
