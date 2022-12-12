<?php

namespace App\Domain\UserFilterSetting;

use App\Infrastructure\UserFilterSetting\UserFilterHandlerRepository;
use Odan\Session\SessionInterface;

class UserFilterHandler
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly UserFilterHandlerRepository $userFilterHandlerRepository
    ) {
    }

    /**
     * Find saved filters from authenticated user
     *
     * @param UserFilterModule $userFilterModule
     * @return array
     */
    public function findFiltersFromAuthenticatedUser(UserFilterModule $userFilterModule): array
    {
        return $this->userFilterHandlerRepository->findFiltersFromUser(
            $this->session->get('user_id'),
            $userFilterModule->value
        );
    }

    /**
     * Remove old filters from db and save given filters
     *
     * @param array|null $filters
     * @param UserFilterModule $userFilterModule
     * @return void
     */
    public function setFilterSettingForAuthenticatedUser(
        ?array $filters,
        UserFilterModule $userFilterModule
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