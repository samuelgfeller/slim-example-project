<?php

namespace App\Application\Actions\User\Ajax;

use App\Application\Responder\Responder;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Post list all and own action.
 */
final class UserListAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param UserFinder $userFinder
     */
    public function __construct(
        private readonly Responder $responder,
        protected readonly UserFinder $userFinder,
    ) {
    }

    /**
     * Client list all and own Action.
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
        // Retrieve posts with given filter values (or none)
        // $clientResultCollection = $this->clientFilterFinder->findClientsWithFilter($request->getQueryParams());
        $userResultDataArray = $this->userFinder->findAllUsersResultDataForList();

        return $this->responder->respondWithJson($response, [
            'userResultDataArray' => $userResultDataArray,
            'statuses' => UserStatus::toArray(),
        ]);
    }
}
