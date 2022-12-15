<?php

namespace App\Domain\FilterSetting;

use App\Infrastructure\UserFilterSetting\UserFilterHandlerRepository;
use Odan\Session\SessionInterface;

class FilterSettingSaver
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly UserFilterHandlerRepository $userFilterHandlerRepository
    ) {
    }

    /**
     * Remove old filters from db and save given filters.
     *
     * @param array|null $filters
     * @param FilterModule $userFilterModule
     *
     * @return void
     */
    public function saveFilterSettingForAuthenticatedUser(
        ?array $filters,
        FilterModule $userFilterModule
    ): void {
        $loggedInUser = $this->session->get('user_id');
        $this->userFilterHandlerRepository->deleteFilterSettingFromUser($loggedInUser, $userFilterModule->value);
        if ($filters !== null && $filters !== []) {
            $userFilterRow = [];
            foreach ($filters as $key => $filterId) {
                $userFilterRow[$key]['user_id'] = $loggedInUser;
                $userFilterRow[$key]['filter_id'] = $filterId;
                $userFilterRow[$key]['module'] = $userFilterModule->value;
            }
            $this->userFilterHandlerRepository->insertUserClientListFilterSetting($userFilterRow);
        }
    }
}
