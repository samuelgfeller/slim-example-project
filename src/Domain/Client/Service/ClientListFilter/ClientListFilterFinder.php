<?php

namespace App\Domain\Client\Service\ClientListFilter;

use App\Domain\Client\Service\ClientListFilter\Data\ClientListFilterData;

class ClientListFilterFinder
{
    public function __construct(
        private readonly ClientListFilterGetter $clientListFilterGenerator,
        private readonly UserClientListFilterHandler $userClientListFilterHandler,
    ) {
    }

    /**
     * Returns active and inactive filters
     *
     * @return array{
     *     active: array{string: ClientListFilterData[]},
     *     inactive: array{string: ClientListFilterData[]}
     *     }
     */
    public function findClientListFilters(): array
    {
        // Get all available filters
        $allClientFilters = $this->clientListFilterGenerator->getClientListFilters();

        $returnArray['active'] = [];
        // Check which filters are active in session
        if (($activeFilters = $this->userClientListFilterHandler->findFiltersFromAuthenticatedUser()) !== null) {
            foreach ($activeFilters as $activeFilterId) {
                // Add to session active filters only if it exists in $allClientFilters and is authorized
                if (isset($allClientFilters[$activeFilterId]) && $allClientFilters[$activeFilterId]->authorized) {
                    $category = $allClientFilters[$activeFilterId]->category;
                    $returnArray['active'][$category][$activeFilterId] = $allClientFilters[$activeFilterId];
                    // Remove filter from $allClientFilters if it's an active filter
                    unset($allClientFilters[$activeFilterId]);
                }
            }
        }
        // Add active filters to session (refresh in case there was an old filter that doesn't exist anymore)
        $this->userClientListFilterHandler->setClientListFilterSettingForAuthenticatedUser(array_keys($returnArray['active']));
        // Inactive are the ones that were not added to 'active' previously
        foreach ($allClientFilters as $filterId => $filterData) {
            // Add to inactive if authorized
            if ($filterData->authorized === true) {
                $returnArray['inactive'][$filterData->category][$filterId] = $filterData;
            }
        }

        return $returnArray;
    }
}