<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserFetchListAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private UserFinder $userFinder,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        // Retrieve users
        $userResultDataArray = $this->userFinder->findAllUsersResultDataForList();

        return $this->jsonResponder->encodeAndAddToResponse($response, [
            'userResultDataArray' => $userResultDataArray,
            'statuses' => UserStatus::getAllDisplayNames(),
        ]);
    }
}
