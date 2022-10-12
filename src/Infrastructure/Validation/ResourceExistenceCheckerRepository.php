<?php


namespace App\Infrastructure\Validation;


use App\Infrastructure\Factory\QueryFactory;

/**
 * Check if given resource exists
 */
class ResourceExistenceCheckerRepository
{

    public function __construct(
        private readonly QueryFactory $queryFactory
    ) { }

    /**
     * Check existence of given resource
     *
     * @param int $id
     * @param string $table
     * @return bool
     */
    public function rowExists(int $id, string $table): bool
    {
        $query = $this->queryFactory->newQuery()->from($table);
        $query->select(1)->where(['id' => $id]);
        $row = $query->execute()->fetch();
        return !empty($row);
    }
}