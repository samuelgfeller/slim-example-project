<?php

namespace App\Application\Actions\User;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Service\UserRoleFinder;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class UserListPageAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly UserFinder $userFinder,
        private readonly UserRoleFinder $userRoleFinder,
    ) {
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
     * @throws \Throwable
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // Retrieve users
        $users = $this->userFinder->findAllUsers();
        return $this->responder->render($response, 'user/user-list.html.php', [
            'users' => $users,
            'userStatuses' => UserStatus::cases(),
            // 'userStatuses' => array_column(UserStatus::cases(), 'value'),
            'userRoles' => $this->userRoleFinder->findAllUserRolesForDropdown(),
        ]);
    }
}
