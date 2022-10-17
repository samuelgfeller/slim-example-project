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
     * @return array
     */
    public function findClientListFilters(): array
    {
        $allClientListFilters = $this->clientListFilterFinderRepository->findAllClientListFilters();
        $this->session->set('client_list_filter', [1, 2, 3]);
        $returnArray = [];
        if (($activeFilters = $this->session->get('client_list_filter')) !== null) {
            foreach ($activeFilters as $activeFilterId) {
                $returnArray['active'][$activeFilterId] = $allClientListFilters[$activeFilterId];
                // Remove filter from $allClientListFilters if it's an active filter
                unset($allClientListFilters[$activeFilterId]);
            }
        }
        $returnArray['inactive'] = $allClientListFilters;

        return $returnArray;
    }
}