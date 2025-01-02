<?php

namespace App\Modules\User\Action\Ajax;

use App\Core\Application\Responder\JsonResponder;
use App\Modules\User\Enum\UserStatus;
use App\Modules\User\Service\UserFinder;
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
