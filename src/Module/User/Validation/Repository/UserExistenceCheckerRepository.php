<?php

namespace App\Module\User\Validation\Repository;

use App\Infrastructure\Database\QueryFactory;

readonly class UserExistenceCheckerRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Checks if user with given email already exists.
     *
     * @param string $email
     * @param int|null $userIdToExclude exclude user that already has the email from check (for update)
     *
     * @return bool
     */
    public function userWithEmailAlreadyExists(string $email, ?int $userIdToExclude = null): bool
    {
        $query = $this->queryFactory->selectQuery()->select(['id'])->from('user')->andWhere(
            ['deleted_at IS' => null, 'email' => $email]
        );

        if ($userIdToExclude !== null) {
            $query->andWhere(['id !=' => $userIdToExclude]);
        }

        return $query->execute()->fetch('assoc') !== false;
    }
}
