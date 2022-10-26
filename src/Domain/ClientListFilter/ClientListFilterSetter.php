<?php

namespace App\Domain\ClientListFilter;

use App\Infrastructure\Client\ClientListFilter\ClientListFilterFinderRepository;
use Odan\Session\SessionInterface;

class ClientListFilterSetter
{
    public function __construct(
        private readonly ClientListFilterFinderRepository $clientListFilterFinderRepository,
        private readonly SessionInterface $session,
    ) {
    }

    /**
     * Returns active and inactive list filters
     *
     * @return array
     */
    public function findClientListFilters(): array
    {
        $allClientFilters = $this->clientListFilterFinderRepository->findAllClientListFilters();
        // $this->session->set('client_list_filter', [1, 2, 3]);
        $returnArray['active'] = [];
        // Check which filters are active in session
        if (($activeFilters = $this->session->get('client_list_filter')) !== null) {
            foreach ($activeFilters as $activeFilterId) {
                $returnArray['active'][$activeFilterId] = $allClientFilters[$activeFilterId];
                // Remove filter from $allClientFilters if it's an active filter
                unset($allClientFilters[$activeFilterId]);
            }
        }
        $returnArray['inactive'] = $allClientFilters;

        return $returnArray;
    }
}