<?php

namespace App\Domain\ClientListFilter;

use App\Domain\ClientListFilter\Data\ClientListFilterData;
use Odan\Session\SessionInterface;

class ClientListFilterFinder
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly ClientListFilterGenerator $clientListFilterGenerator,
    ) {
    }

    /**
     * Returns active and inactive list filters
     *
     * @return array{
     *     active: array{string: ClientListFilterData[]},
     *     inactive: array{string: ClientListFilterData[]}
     *     }
     */
    public function findClientListFilters(): array
    {
        $allClientFilters = $this->clientListFilterGenerator->generateClientListFilter();

        $returnArray['active'] = [];
        // Check which filters are active in session
        if (($activeFilters = $this->session->get('client_list_filter')) !== null) {
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
        $this->session->set('client_list_filter', array_keys($returnArray['active']));
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