<?php


namespace App\Infrastructure\Client\UserClientListFilter;

use App\Infrastructure\Factory\QueryFactory;

class UserClientListFilterFinderRepository
{

    public function __construct(
        private readonly QueryFactory $queryFactory,
    ) {
    }

    /**
     * Return filters from user
     */
    public function findFiltersFromUser(int|string $userId): array
    {
        $query = $this->queryFactory->newQuery()->select(['filter_id'])->from('user_client_list_filter')
            ->where(['user_id' => $userId]);
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        $filterIds = array_column($resultRows, 'filter_id');
        return $filterIds;
    }

    /**
     * Removes all filters previously saved from user
     *
     * @param int|string $userId
     * @return bool
     */
    public function deleteFilterSettingFromUser(int|string $userId): bool
    {
        $query = $this->queryFactory->newQuery()->delete('user_client_list_filter')->where(['user_id' => $userId]);
        return $query->execute()->rowCount() > 0;
    }

    /**
     * Inserts client list filters
     *
     * @param array $userFilterRow
     */
    public function insertUserClientListFilterSetting(array $userFilterRow): void
    {
        $this->queryFactory->newMultipleInsert($userFilterRow)->into('user_client_list_filter')
            ->execute()->lastInsertId();
    }
}