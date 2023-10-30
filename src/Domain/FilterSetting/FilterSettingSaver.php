<?php

namespace App\Domain\FilterSetting;

use App\Application\Data\UserNetworkSessionData;
use App\Domain\FilterSetting\Repository\UserFilterHandlerRepository;

class FilterSettingSaver
{
    public function __construct(
        private readonly UserFilterHandlerRepository $userFilterHandlerRepository,
        private readonly UserNetworkSessionData $userNetworkSessionData,
    ) {
    }

    /**
     * Remove old filters from db and save given filters.
     * This function should really be called only once per request otherwise
     * only the filters from the last call will be saved.
     *
     * @param array|null $filters
     * @param FilterModule $userFilterModule
     *
     * @return void
     */
    public function saveFilterSettingForAuthenticatedUser(
        ?array $filters,
        FilterModule $userFilterModule,
    ): void {
        // Delete previous active filters in database before adding the new ones
        $this->userFilterHandlerRepository->deleteFilterSettingFromUser(
            $this->userNetworkSessionData->userId,
            $userFilterModule->value
        );
        if ($filters !== null && $filters !== []) {
            $userFilterRow = [];
            foreach ($filters as $key => $filterId) {
                $userFilterRow[$key]['user_id'] = $this->userNetworkSessionData->userId;
                $userFilterRow[$key]['filter_id'] = $filterId;
                $userFilterRow[$key]['module'] = $userFilterModule->value;
            }
            $this->userFilterHandlerRepository->insertUserClientListFilterSetting($userFilterRow);
        }
    }
}
