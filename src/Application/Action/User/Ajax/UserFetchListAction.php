<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Responder\JsonEncoder;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserFetchListAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private UserFinder $userFinder,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // Retrieve clients with given filter values (or none)
        // $clientResultCollection = $this->clientFilterFinder->findClientsWithFilter($request->getQueryParams());
        $userResultDataArray = $this->userFinder->findAllUsersResultDataForList();

        return $this->jsonEncoder->encodeAndAddToResponse($response, [
            'userResultDataArray' => $userResultDataArray,
            'statuses' => UserStatus::toTranslatedNamesArray(),
        ]);
    }
}
