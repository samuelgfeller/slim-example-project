<?php

namespace App\Module\Dashboard\Display\Service;

use App\Module\FilterSetting\Enum\FilterModule;
use App\Module\FilterSetting\Find\Data\FilterData;
use App\Module\FilterSetting\Find\Service\FilterSettingFinder;
use App\Module\User\FindAbbreviatedNameList\Service\AbbreviatedUserNameListFinder;
use App\Module\UserActivity\ReadAuthorization\Service\UserActivityReadAuthorizationChecker;
use Odan\Session\SessionInterface;

final readonly class UserFilterChipProvider
{
    public function __construct(
        private FilterSettingFinder $filterSettingFinder,
        private SessionInterface $session,
        private AbbreviatedUserNameListFinder $abbreviatedUserNameListFinder,
        private UserActivityReadAuthorizationChecker $userActivityReadAuthorizationChecker,
    ) {
    }

    /**
     * Returns filter chip HTML including container and button.
     *
     * @return string
     */
    public function getUserFilterChipsHtml(): string
    {
        $filters = $this->getActiveAndInactiveUserFilters();
        $activeFilterChips = '';
        foreach ($filters['active'] as $filterCategory => $filtersInCategory) {
            /** @var FilterData $filterData */
            foreach ($filtersInCategory as $filterId => $filterData) {
                $activeFilterChips .= "<div class='filter-chip filter-chip-active'>\n
                               <span data-filter-id='$filterId' data-param-name='$filterData->paramName'
                              data-param-value='$filterData->paramValue'
                              data-category='$filterData->category'>$filterData->name</span>\n
                              </div>";
            }
        }
        $inactiveFilterChips = '';
        foreach ($filters['inactive'] as $filterCategory => $filtersInCategory) {
            $inactiveFilterChips .=
                "<span class='filter-chip-container-label' data-category='$filterCategory'>$filterCategory</span>";
            /** @var FilterData $filterData */
            foreach ($filtersInCategory as $filterId => $filterData) {
                $inactiveFilterChips .= "<div class='filter-chip'>
                <span data-filter-id='$filterId' data-param-name='$filterData->paramName'
                      data-param-value='$filterData->paramValue' data-category='$filterData->category'
                >$filterData->name</span>
                </div>\n";
            }
        }

        return "<div class='filter-chip-container'><div id='active-user-filter-chips-div' class='active-filter-chips-div'>
            <button id='add-filter-btn'>+ Filter</button>
            $activeFilterChips
            </div>
            <div id='available-filter-div'>
            <span id='no-more-available-filters-span'>No more filters</span>
            $inactiveFilterChips
            </div></div>";
    }

    /**
     * Returns active and inactive filters.
     *
     * @return array{
     *     active: array<string, FilterData[]>,
     *     inactive: array<string, FilterData[]>
     *     }
     */
    public function getActiveAndInactiveUserFilters(): array
    {
        return $this->filterSettingFinder->getActiveAndInactiveFilters(
            $this->getUserActivityFilters(),
            FilterModule::DASHBOARD_USER_ACTIVITY
        );
    }

    /**
     * Provides all users as FilterData for filter chips.
     *
     * @return FilterData[]
     */
    private function getUserActivityFilters(): array
    {
        $loggedInUserId = $this->session->get('user_id');

        $abbreviatedUserNames = $this->abbreviatedUserNameListFinder->findAbbreviatedUserNamesList();
        $userFilters = [];
        foreach ($abbreviatedUserNames as $userId => $abbreviatedUserName) {
            // All users except authenticated user as the own activity is not that pertinent
            if ($userId !== $loggedInUserId) {
                $userFilters["user_$userId"] = new FilterData([
                    'name' => $abbreviatedUserName,
                    'paramName' => 'user',
                    'paramValue' => $userId,
                    'category' => null,
                    'authorized' => $this->userActivityReadAuthorizationChecker->isGrantedToReadUserActivity((int)$userId, false),
                ]);
            }
        }

        return $userFilters;
    }
}
