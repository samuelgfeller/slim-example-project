<?php

namespace App\Infrastructure\Validation;

use App\Infrastructure\Factory\QueryFactory;

/**
 * Check if given resource exists.
 */
class ResourceExistenceCheckerRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Check existence of given resource.
     *
     * @param array $whereAttr cake query builder where attributes
     * @param string $table
     * @param bool $excludingDeletedAt true if entries with deleted_at not null should not
     * be taken into account which is default
     *
     * @return bool
     */
    public function rowExists(array $whereAttr, string $table, bool $excludingDeletedAt = true): bool
    {
        $deletedAtAttr = $excludingDeletedAt ? ['deleted_at IS' => null] : [];
        $query = $this->queryFactory->newQuery()->from($table);
        $query->select(1)->where(array_merge($whereAttr, $deletedAtAttr));
        $row = $query->execute()->fetch();

        return !empty($row);
    }
}
