<?php

namespace App\Module\UserActivity\Create\Service;

use App\Core\Application\Data\UserNetworkSessionData;
use App\Module\User\Data\UserActivityData;
use App\Module\User\Enum\UserActivity;
use App\Module\UserActivity\Create\Repository\UserActivityCreatorRepository;

final readonly class UserActivityLogger
{
    public function __construct(
        private UserActivityCreatorRepository $userActivityCreatorRepository,
        private UserNetworkSessionData $userNetworkSessionData,
    ) {
    }

    /**
     * Insert new user activity.
     *
     * @param UserActivity $userActivityAction
     * @param string $table A better name should be found as the service should not know the table name
     * @param int|null $rowId
     * @param array|null $data
     * @param int|null $userId in case there is no session like on login
     *
     * @return int
     */
    public function logUserActivity(
        UserActivity $userActivityAction,
        string $table,
        ?int $rowId,
        ?array $data = null,
        ?int $userId = null,
    ): int {
        $userActivity = new UserActivityData();
        $userActivity->ipAddress = $this->userNetworkSessionData->ipAddress;
        $userActivity->userAgent = $this->userNetworkSessionData->userAgent;
        $userActivity->userId = $this->userNetworkSessionData->userId ?? $userId;
        $userActivity->action = $userActivityAction;
        $userActivity->table = $table;
        $userActivity->rowId = $rowId;
        $userActivity->data = $data;

        return $this->userActivityCreatorRepository->insertUserActivity($userActivity->toArrayForDatabase());
    }
}
