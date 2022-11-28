<?php

namespace App\Domain\User\Service;

use App\Domain\User\Authorization\UserAuthorizationChecker;
use App\Domain\User\Data\UserActivityData;
use App\Domain\User\Enum\UserActivityAction;
use App\Infrastructure\User\UserActivityRepository;
use Odan\Session\SessionInterface;

class UserActivityManager
{
    public function __construct(
        private readonly UserActivityRepository $userActivityRepository,
        private readonly SessionInterface $session,
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
    ) {
    }

    /**
     * Insert new user activity
     *
     * @param UserActivityAction $userActivityAction
     * @param string $table
     * @param int $rowId
     * @param array $data
     * @return int
     */
    public function addUserActivity(
        UserActivityAction $userActivityAction,
        string $table,
        int $rowId,
        array $data = null
    ): int {
        $userActivity = new UserActivityData();
        $userActivity->ip_address = $_SERVER['REMOTE_ADDR'];
        $userActivity->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $userActivity->user_id = $this->session->get('user_id');
        $userActivity->action = $userActivityAction;
        $userActivity->table = $table;
        $userActivity->row_id = $rowId;
        $userActivity->data = $data;

        return $this->userActivityRepository->insertUserActivity($userActivity->toArray());
    }

    /**
     * Delete entry
     *
     * @param int $activityId
     * @return bool if deleted
     */
    public function deleteUserActivity(int $activityId): bool
    {
        return $this->userActivityRepository->deleteUserActivity($activityId);
    }

    /**
     * Find user activity entries for the user read page
     * @param int $userId
     * @return UserActivityData[]
     */
    public function findUserActivityReport(int $userId): array
    {
        if ($this->userAuthorizationChecker->isGrantedToReadUserActivity($userId)) {
            return $this->userActivityRepository->findUserActivities($userId);
        }
        return [];
    }
}