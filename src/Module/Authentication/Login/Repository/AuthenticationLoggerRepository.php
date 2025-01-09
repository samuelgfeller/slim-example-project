<?php

namespace App\Module\Authentication\Login\Repository;

use App\Core\Infrastructure\Database\QueryFactory;

final readonly class AuthenticationLoggerRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Insert new login request.
     *
     * @param string $email
     * @param ?string $ip
     * @param bool $success whether login request was a successful login or not
     * @param int|null $userId
     *
     * @return int
     */
    public function logLoginRequest(string $email, ?string $ip, bool $success, ?int $userId = null): int
    {
        $query = $this->queryFactory->insertQuery();
        $query->insert(['email', 'ip_address', 'is_success', 'user_id', 'created_at'])
            ->into('authentication_log')
            ->values([
                'email' => $email,
                'ip_address' => $ip,
                'is_success' => $success === true ? 1 : 0,
                'user_id' => $userId,
                // Set time in PHP to be sure that time matches php timezone
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);

        return (int)$query->execute()->lastInsertId();
    }
}
