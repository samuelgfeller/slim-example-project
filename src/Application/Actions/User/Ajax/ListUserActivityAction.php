<?php

namespace App\Application\Actions\User\Ajax;

use App\Application\Responder\Responder;
use App\Domain\FilterSetting\FilterModule;
use App\Domain\FilterSetting\FilterSettingSaver;
use App\Domain\User\Service\UserActivityFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListUserActivityAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param UserActivityFinder $userActivityFinder
     * @param SessionInterface $session
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly UserActivityFinder $userActivityFinder,
        private readonly SessionInterface $session,
        private readonly FilterSettingSaver $filterSettingSaver,
    ) {
    }

    /**
     * Client list all and own Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args
     * @return ResponseInterface The response
     * @throws \JsonException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $queryParams = $request->getQueryParams();
        // User ids may be an array or a single value
        $userIds = $queryParams['user'] ?? null;

        $userResultDataArray = $this->userActivityFinder->findUserActivityReport($userIds);

        // If filter ids are provided they have to be saved too
        if (isset($queryParams['filterIds'])) {
            $this->filterSettingSaver->saveFilterSettingForAuthenticatedUser(
                $queryParams['filterIds'] ?? null,
                FilterModule::DASHBOARD_USER_ACTIVITY
            );
        }

        return $this->responder->respondWithJson($response, $userResultDataArray);
    }
}