<?php

namespace App\Domain\User\Service;

use App\Domain\User\Authorization\UserAuthorizationChecker;
use App\Domain\User\Data\UserActivityData;
use App\Domain\User\Enum\UserActivity;
use App\Infrastructure\User\UserActivityRepository;
use InvalidArgumentException;
use Odan\Session\SessionInterface;
use RuntimeException;
use Slim\Interfaces\RouteParserInterface;

class UserActivityManager
{
    public function __construct(
        private readonly UserActivityRepository $userActivityRepository,
        private readonly SessionInterface $session,
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
        private readonly RouteParserInterface $routeParser,
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

    /**
     * Find user activity entries for the user read page
     * @param int $userId
     * @return UserActivityData[]
     */
    public function findUserActivityReport(int $userId): array
    {
        if ($this->userAuthorizationChecker->isGrantedToReadUserActivity($userId)) {
            $userActivities = $this->userActivityRepository->findUserActivities($userId);
            // return $userActivities;

            // Group user activities by date
            $groupedActivitiesByDate = [];
            foreach ($userActivities as $userActivity) {
                try {
                    // Generate read url. The route name HAS to be in the following format: "[table_name]-read-page"
                    // and the url argument has to be called "[table-name]-id"
                    $userActivity->pageUrl = $this->routeParser->urlFor(
                        "$userActivity->table-read-page",
                        [$userActivity->table . '_id' => $userActivity->row_id]
                    );
                } catch (RuntimeException|InvalidArgumentException $exception) {
                    $userActivity->pageUrl = null;
                }
                // Add the time and action name
                $userActivity->timeAndActionName = $userActivity->datetime->format('H:i') . ": " .
                    ucfirst($userActivity->action->value);

                $groupedActivitiesByDate[$userActivity->datetime->format('d. F Y')][] = $userActivity;
            }
            return $groupedActivitiesByDate;
        }
        return [];
    }


}