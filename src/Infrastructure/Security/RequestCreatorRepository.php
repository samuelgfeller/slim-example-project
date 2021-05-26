<?php


namespace App\Infrastructure\Security;


use App\Infrastructure\Factory\QueryFactory;

class RequestCreatorRepository
{
    public function __construct(
        private QueryFactory $queryFactory
    ) { }

    /**
     * Insert new request where an email was sent
     *
     * @param string $email Has to be validated first
     * @param string $ip
     * @return string
     */
    public function insertEmailRequest(string $email, string $ip): string
    {
        $query = $this->queryFactory->newQuery();

        return $query->insert(['email', 'ip_address', 'sent_email', 'is_login'])->into('request_track')->values(
            [
                'email' => $email,
                'ip_address' => $query->newExpr("INET_ATON(:ip)"),
                'sent_email' => true,
                'is_login' => null,
            ]
        )->bind(':ip', $ip, 'string')->execute()->lastInsertId();
    }

    /**
     * Insert new login request
     *
     * @param string $email
     * @param string $ip
     * @param bool $success whether login request was a successful login or not
     * @return string
     */
    public function insertLoginRequest(string $email, string $ip, bool $success): string
    {
        $query = $this->queryFactory->newQuery();
        $query->insert(['email', 'ip_address', 'sent_email', 'is_login'])->into('request_track')->values(
            [
                'email' => $email,
                'ip_address' => $query->newExpr("INET_ATON(:ip)"),
                'sent_email' => 0,
                'is_login' => $success === true ? 'success' : 'failure',
            ]
        )->bind(':ip', $ip, 'string');
        return $query->execute()->lastInsertId();
    }
}