<?php

namespace App\Application\Actions\User\Page;

use App\Application\Responder\Responder;
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
     * @param UserFinder $userFinder
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly UserFinder $userFinder,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @throws \JsonException
     * @throws \Throwable
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // Retrieve users
        $users = $this->userFinder->findAllUsersResultDataForList();

        return $this->responder->render($response, 'user/user-list.html.php', [
            'users' => $users,
            'userStatuses' => UserStatus::cases(),
        ]);
    }
}
