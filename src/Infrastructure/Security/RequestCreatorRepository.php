<?php

namespace App\Infrastructure\Security;

use App\Infrastructure\Factory\QueryFactory;

class RequestCreatorRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Insert new request where an email was sent.
     *
     * @param string $email Has to be validated first
     * @param string $ip
     *
     * @return string
     */
    public function insertEmailRequest(string $email, string $ip): string
    {
        $query = $this->queryFactory->newQuery();

        return $query->insert(['email', 'ip_address', 'sent_email', 'is_login', 'created_at'])
            ->into('user_request')->values([
                'email' => $email,
                'ip_address' => $query->newExpr('INET_ATON(:ip)'),
                'sent_email' => true,
                'is_login' => null,
                // Set time in PHP to be sure that time matches php timezone
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ])->bind(':ip', $ip, 'string')->execute()->lastInsertId();
    }

    /**
     * Insert new login request.
     *
     * @param string $email
     * @param string $ip
     * @param bool $success whether login request was a successful login or not
     *
     * @return string
     */
    public function insertLoginRequest(string $email, string $ip, bool $success): string
    {
        $query = $this->queryFactory->newQuery();
        $query->insert(['email', 'ip_address', 'sent_email', 'is_login', 'created_at'])->into('user_request')
            ->values([
                'email' => $email,
                'ip_address' => $query->newExpr('INET_ATON(:ip)'),
                'sent_email' => 0,
                'is_login' => $success === true ? 'success' : 'failure',
                // Set time in PHP to be sure that time matches php timezone
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ])->bind(':ip', $ip, 'string');

        return $query->execute()->lastInsertId();
    }
}
