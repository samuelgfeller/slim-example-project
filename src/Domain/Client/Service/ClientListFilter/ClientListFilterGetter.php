<?php

namespace App\Domain\Client\Service\ClientListFilter;

use App\Domain\Authorization\AuthorizationChecker;
use App\Domain\Client\Service\ClientListFilter\Data\ClientListFilterData;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Service\UserNameAbbreviator;
use App\Infrastructure\Client\ClientStatus\ClientStatusFinderRepository;
use App\Infrastructure\User\UserFinderRepository;
use Odan\Session\SessionInterface;

class ClientListFilterGetter
{
    public function __construct(
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
        private readonly UserNameAbbreviator $userNameAbbreviator,
        private readonly UserFinderRepository $userFinderRepository,
        private readonly SessionInterface $session,
        private readonly AuthorizationChecker $authorizationChecker,
    ) {
    }

    /**
     * Returns default filters
     *
     * @return ClientListFilterData[]
     */
    public function getClientListFilters(): array
    {
        $loggedInUserId = $this->session->get('user_id');

        // Basic client filters
        $allClientFilters = [
            // Category
            'unassigned' => new ClientListFilterData([
                'name' => 'Unassigned',
                'paramName' => 'user',
                'paramValue' => null,
                'category' => null,
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER),
            ]),
            'assigned_to_me' => new ClientListFilterData([
                'name' => 'Assigned to me',
                'paramName' => "user",
                'paramValue' => $loggedInUserId,
                'category' => null,
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER),
            ]),
            'deleted' => new ClientListFilterData([
                'name' => 'Deleted',
                'paramName' => "deleted",
                'paramValue' => 1,
                'category' => null,
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(
                    UserRole::MANAGING_ADVISOR
                ),
            ]),
        ];
        // Add all statuses to filters
        $clientStatuses = $this->clientStatusFinderRepository->findAllClientStatusesMappedByIdName();
        foreach ($clientStatuses as $id => $name) {
            $allClientFilters["status_$id"] = new ClientListFilterData([
                'name' => $name,
                'paramName' => "status",
                'paramValue' => $id,
                'category' => 'Status',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER)
            ]);
        }
        $abbreviatedUserNames = $this->userNameAbbreviator->abbreviateUserNames(
            $this->userFinderRepository->findAllUsers()
        );
        foreach ($abbreviatedUserNames as $userId => $name) {
            // All users except authenticated user as there is already a filter "Assigned to me"
            if ($userId !== $loggedInUserId) {
                $allClientFilters["user_$userId"] = new ClientListFilterData([
                    'name' => "Assigned to $name",
                    'paramName' => "user",
                    'paramValue' => $userId,
                    'category' => 'Other user',
                    'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER)
                ]);
            }
        }
        return $allClientFilters;
    }
}