<?php

namespace App\Module\UserActivity\List\Service;

use App\Module\User\Find\Service\UserFinder;
use App\Module\UserActivity\List\Repository\UserActivityListFinderRepository;
use App\Module\UserActivity\ReadAuthorization\Service\UserActivityReadAuthorizationChecker;
use IntlDateFormatter;
use InvalidArgumentException;
use RuntimeException;
use Slim\Interfaces\RouteParserInterface;

final readonly class UserActivityListFinder
{
    public function __construct(
        private UserActivityReadAuthorizationChecker $userActivityReadAuthorizationChecker,
        private RouteParserInterface $routeParser,
        private UserFinder $userFinder,
        private UserActivityListFinderRepository $userActivityRepository,
    ) {
    }

    /**
     * Find user activity entries for the user read page.
     *
     * @param array|int|string|null $userIds array of user ids or user id
     *
     * @return array
     */
    public function findUserActivityReport(array|int|string|null $userIds): array
    {
        if ($userIds) {
            return $this->findUserActivitiesGroupedByDate($userIds);
        }

        return [];
    }

    /**
     * Find user activities grouped by date
     * Either for one user or for multiple users (dashboard panel) in which case
     * the user name precedes the time and action name.
     *
     * @param int|string|array $userIds
     *
     * @return array
     */
    private function findUserActivitiesGroupedByDate(int|string|array $userIds): array
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }
        $grantedUserIds = [];
        foreach ($userIds as $userId) {
            if ($this->userActivityReadAuthorizationChecker->isGrantedToReadUserActivity((int)$userId)) {
                $grantedUserIds[] = (int)$userId;
            }
        }

        $userActivities = $this->userActivityRepository->findUserActivities($grantedUserIds);
        // Group user activities by date
        $groupedActivitiesByDate = [];
        // Init date formatter that is needed to display the date with the correct language
        $dateFormatter = new IntlDateFormatter(
            setlocale(LC_ALL, 0) ?: null,
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE
        );
        foreach ($userActivities as $userActivity) {
            try {
                // Generate read url. The route name HAS to be in the following format: "[table_name]-read-page"
                // and the url argument has to be called "[table-name]-id"
                $userActivity->pageUrl = $this->routeParser->urlFor(
                    "$userActivity->table-read-page",
                    [$userActivity->table . '_id' => (string)$userActivity->rowId]
                );
            } catch (RuntimeException|InvalidArgumentException $exception) {
                $userActivity->pageUrl = null;
            }
            // Add the time and action name
            $actionVal = $userActivity->action->getDisplayName();
            // ucfirst does not work for non english chars. Below is an equivalent that also works for german chars.
            $ucFirstActionValue = mb_strtoupper(mb_substr($actionVal, 0, 1)) . mb_substr($actionVal, 1);
            $userActivity->timeAndActionName = $userActivity->datetime?->format('H:i') . ': ' . $ucFirstActionValue;
            // If there are multiple users, add the user name before time and action name
            if (count($userIds) > 1) {
                $userData = $this->userFinder->findUserById((int)$userActivity->userId);
                $userActivity->timeAndActionName =
                    '<span style="color: var(--black-white-text-color)">' . $userData->getFullName() . '</span> • ' .
                    $userActivity->timeAndActionName;
            }
            $formattedDate = ucfirst(
                (string)$dateFormatter->format($userActivity->datetime ?? new \DateTimeImmutable())
            );
            $groupedActivitiesByDate[$formattedDate][] = $userActivity;
        }

        return $groupedActivitiesByDate;
    }
}
