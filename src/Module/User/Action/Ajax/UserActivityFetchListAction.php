<?php

namespace App\Module\User\Action\Ajax;

use App\Core\Application\Responder\JsonResponder;
use App\Module\FilterSetting\Enum\FilterModule;
use App\Module\FilterSetting\Save\Service\FilterSettingSaver;
use App\Module\UserActivity\List\Service\UserActivityListFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserActivityFetchListAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private UserActivityListFinder $userActivityFinder,
        private FilterSettingSaver $filterSettingSaver,
    ) {
    }

    /**
     * Fetch list of user activity.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
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

        return $this->jsonResponder->encodeAndAddToResponse($response, $userResultDataArray);
    }
}
