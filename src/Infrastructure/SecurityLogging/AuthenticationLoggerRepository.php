<?php

namespace App\Infrastructure\SecurityLogging;

use App\Infrastructure\Factory\QueryFactory;

class AuthenticationLoggerRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
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
     * @return string
     */
    public function logLoginRequest(string $email, ?string $ip, bool $success, ?int $userId = null): string
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

        return $query->execute()->lastInsertId();
    }
}
