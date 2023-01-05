<?php

namespace App\Domain\FilterSetting;

use App\Domain\FilterSetting\Data\FilterData;
use App\Infrastructure\UserFilterSetting\UserFilterHandlerRepository;
use Odan\Session\SessionInterface;

class FilterSettingFinder
{
    public function __construct(
        private readonly UserFilterHandlerRepository $userFilterHandlerRepository,
        private readonly FilterSettingSaver $filterSettingSaver,
        private readonly SessionInterface $session,
    ) {
    }

    /**
     * Checks in database which filters are active and
     * returns an array with active and inactive filters.
     *
     * @param FilterData[] $allAvailableFilters
     * @param FilterModule $filterModule
     *
     * @return array{
     *     active: array{string: FilterData[]},
     *     inactive: array{string: FilterData[]}
     *     }
     */
    public function getActiveAndInactiveFilters(array $allAvailableFilters, FilterModule $filterModule): array
    {
        $returnArray = ['active' => [], 'inactive' => []];
        // Check which filters are active in session
        if (($activeFilters = $this->findFiltersFromAuthenticatedUser($filterModule)) !== null) {
            foreach ($activeFilters as $activeFilterId) {
                // Add to active filters only if it exists in $allClientFilters and is authorized
                if (isset($allAvailableFilters[$activeFilterId]) && $allAvailableFilters[$activeFilterId]->authorized) {
                    $category = $allAvailableFilters[$activeFilterId]->category;
                    // Group filters by category (null is a valid array key (becomes ''))
                    $returnArray['active'][$category][$activeFilterId] = $allAvailableFilters[$activeFilterId];
                    // Remove filter from $allClientFilters if it's an active filter
                    unset($allAvailableFilters[$activeFilterId]);
                }
            }
        }

        $activeFilterIds = [];
        foreach ($returnArray['active'] as $category => $activeFilters) {
            // Create array with active filter ids from returnArray to save them all at once as
            // saveFilterSettingForAuthenticatedUser must be called only once
            foreach ($activeFilters as $filterId => $filterValues) {
                // The IDE prefers a nested foreach than array_merge
                $activeFilterIds[] = $filterId;
            }
        }

        // Add active filters to database (refresh in case there was an old filters that don't exist anymore)
        $this->filterSettingSaver->saveFilterSettingForAuthenticatedUser($activeFilterIds, $filterModule);

        // Inactive are the ones that were not added to 'active' previously
        foreach ($allAvailableFilters as $filterId => $filterData) {
            // Add to inactive if authorized
            if ($filterData->authorized === true) {
                $returnArray['inactive'][$filterData->category][$filterId] = $filterData;
            }
        }

        return $returnArray;
    }

    /**
     * Find saved filters from authenticated user.
     *
     * @param FilterModule $userFilterModule
     *
     * @return array
     */
    public function findFiltersFromAuthenticatedUser(FilterModule $userFilterModule): array
    {
        return $this->userFilterHandlerRepository->findFiltersFromUser(
            $this->session->get('user_id'),
            $userFilterModule->value
        );
    }
}
