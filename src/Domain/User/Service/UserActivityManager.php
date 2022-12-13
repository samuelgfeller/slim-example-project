<?php

namespace App\Domain\User\Service;

use App\Domain\User\Data\UserActivityData;
use App\Domain\User\Enum\UserActivity;
use App\Infrastructure\User\UserActivityRepository;
use Odan\Session\SessionInterface;

class UserActivityManager
{
    public function __construct(
        private readonly UserActivityRepository $userActivityRepository,
        private readonly SessionInterface $session,
    ) {
    }

    /**
     * Insert new user activity
     *
     * @param UserActivity $userActivityAction
     * @param string $table
     * @param int $rowId
     * @param array|null $data
     * @param null|int $userId in case there is no session like on login
     * @return int
     */
    public function addUserActivity(
        UserActivity $userActivityAction,
        string $table,
        int $rowId,
        array $data = null,
        ?int $userId = null,
    ): int {
        $userActivity = new UserActivityData();
        $userActivity->ip_address = $_SERVER['REMOTE_ADDR'];
        $userActivity->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $userActivity->user_id = $this->session->get('user_id') ?? $userId;
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
        return $this->userActivityRepository->hardDeleteUserActivity($activityId);
    }
}