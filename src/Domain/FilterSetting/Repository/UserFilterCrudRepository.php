<?php

namespace App\Domain\FilterSetting\Repository;

use App\Infrastructure\Factory\QueryFactory;

final readonly class UserFilterCrudRepository
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

    /**
     * Removes all filters previously saved from user.
     *
     * @param int|string $userId
     * @param string|null $userFilterModule
     *
     * @return bool
     */
    public function deleteFilterSettingFromUser(int|string $userId, ?string $userFilterModule = null): bool
    {
        $moduleWhere = $userFilterModule ? ['module' => $userFilterModule] : [];
        $query = $this->queryFactory->hardDeleteQuery()->delete('user_filter_setting')
            ->where(array_merge(['user_id' => $userId], $moduleWhere));

        return $query->execute()->rowCount() > 0;
    }

    /**
     * Inserts client list filters.
     *
     * @param array $userFilterRow
     */
    public function insertUserClientListFilterSetting(array $userFilterRow): void
    {
        $this->queryFactory->insertQueryMultipleRows($userFilterRow)->into('user_filter_setting')
            ->execute()->lastInsertId();
    }
}
