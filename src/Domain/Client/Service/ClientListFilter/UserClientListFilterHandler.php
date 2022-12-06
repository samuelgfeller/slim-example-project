<?php

namespace App\Domain\Client\Service\ClientListFilter;

use App\Infrastructure\Client\UserClientListFilter\UserClientListFilterFinderRepository;
use Odan\Session\SessionInterface;

class UserClientListFilterHandler
{
    public function __construct(
        private readonly UserClientListFilterFinderRepository $userClientListFilterFinderRepository,
        private readonly SessionInterface $session,
    )
    {
    }

    /**
     * Find saved filters from authenticated user
     *
     * @return array
     */
    public function findFiltersFromAuthenticatedUser(): array
    {
        return $this->userClientListFilterFinderRepository->findFiltersFromUser($this->session->get('user_id'));
    }

    /**
     * Remove old filters from db and save given filters
     *
     * @param array|null $filters
     * @return void
     */
    public function setClientListFilterSettingForAuthenticatedUser(?array $filters): void
    {
        $loggedInUser = $this->session->get('user_id');
        $this->userClientListFilterFinderRepository->deleteFilterSettingFromUser($loggedInUser);
        if ($filters !== null && $filters !== []) {
            $userFilterRow = [];
            foreach ($filters as $key => $filterId) {
                $userFilterRow[$key]['user_id'] = $loggedInUser;
                $userFilterRow[$key]['filter_id'] = $filterId;
            }
            $this->userClientListFilterFinderRepository->insertUserClientListFilterSetting($userFilterRow);
        }
    }
}