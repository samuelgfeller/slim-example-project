<?php

namespace App\Module\FilterSetting\Find\Repository;

use App\Infrastructure\Database\QueryFactory;

final readonly class UserFilterFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Return active filters from user.
     *
     * @param int|string $userId
     * @param string $userFilterModule
     */
    public function findFiltersFromUser(int|string $userId, string $userFilterModule): array
    {
        $query = $this->queryFactory->selectQuery()->select(['filter_id'])->from('user_filter_setting')
            ->where(['user_id' => $userId, 'module' => $userFilterModule]);
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        $filterIds = array_column($resultRows, 'filter_id');

        return $filterIds;
    }
}
