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

    /**
     * User fetch list action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @throws \JsonException
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // Retrieve clients with given filter values (or none)
        // $clientResultCollection = $this->clientFilterFinder->findClientsWithFilter($request->getQueryParams());
        $userResultDataArray = $this->userFinder->findAllUsersResultDataForList();

        return $this->jsonResponder->respondWithJson($response, [
            'userResultDataArray' => $userResultDataArray,
            'statuses' => UserStatus::toTranslatedNamesArray(),
        ]);
    }
}
