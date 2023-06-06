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
     * @param FilterSettingSaver $filterSettingSaver
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly UserActivityFinder $userActivityFinder,
        private readonly FilterSettingSaver $filterSettingSaver,
    ) {
    }

    /**
     * Client list all and own Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @return ResponseInterface The response
     * @throws \JsonException
     *
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

        // Filter ids have to be saved too but only if there are query params
        // otherwise the saved dashboard filter settings are deleted when loading user read
        if (isset($queryParams['filterIds'])) {
            $this->filterSettingSaver->saveFilterSettingForAuthenticatedUser(
                $queryParams['filterIds'],
                FilterModule::DASHBOARD_USER_ACTIVITY
            );
        }

        return $this->responder->respondWithJson($response, $userResultDataArray);
    }
}
