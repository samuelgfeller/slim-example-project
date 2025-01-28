<?php

namespace App\Module\FilterSetting\Save\Repository;

use App\Infrastructure\Database\QueryFactory;

final readonly class UserFilterSaverRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
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
