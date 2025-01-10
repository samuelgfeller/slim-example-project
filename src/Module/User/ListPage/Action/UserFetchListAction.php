<?php

namespace App\Module\User\ListPage\Action;

use App\Core\Application\Responder\JsonResponder;
use App\Module\User\Enum\UserStatus;
use App\Module\User\ListPage\Service\UserListPageFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserFetchListAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private UserListPageFinder $userListFinder,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        // Retrieve users
        $userResultDataArray = $this->userListFinder->findAllUsersResultDataForList();

        return $this->jsonResponder->encodeAndAddToResponse($response, [
            'userResultDataArray' => $userResultDataArray,
            'statuses' => UserStatus::getAllDisplayNames(),
        ]);
    }
}
