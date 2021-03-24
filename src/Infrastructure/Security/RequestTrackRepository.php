<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\User\User;
use App\Infrastructure\DataManager;
use Cake\Database\Connection;

class RequestTrackRepository extends DataManager
{

    public function __construct(Connection $conn = null)
    {
        parent::__construct($conn);
        $this->table = 'request_track';
    }

    /**
     * @param string $email
     * @param string $ip
     * @param bool $sentEmail whether an email was sent or not
     * @return string
     */
    public function newRequest(string $email, string $ip, bool $sentEmail): string
    {
        $query = $this->newInsertQuery();

        return $query->insert(
            [
                'email',
                'ip_address',
                'sent_email',
            ]
        )->values(
            [
                'email' => $email,
                'ip_address' => $query->newExpr("INET_ATON(:ip)"),
                'sent_email' => $sentEmail,
            ]
        )->bind(':ip', $ip, 'string')->execute()->lastInsertId();
    }

    /**
     * Retrieves info about request from the last given amount of seconds
     *
     * @param int $seconds
     * @return array[
     *     'request_amount' => int,
     *     'sent_emails' => int,
     *     'login_failures' => int
     *     'login_successes' => int
     * ]
     */
    public function getGlobalRequestStats(int $seconds): array
    {
        $query = $this->newSelectQuery();
        $query->select(
            [
                'request_amount' => $query->func()->count(1),
                'sent_emails' => $query->func()->sum('sent_email'),
                'login_failures' => $query->func()->sum('CASE WHEN is_login LIKE "failure" THEN 1 ELSE 0 END'),
                'login_successes' => $query->func()->sum('CASE WHEN is_login LIKE "success" THEN 1 ELSE 0 END'),
            ]
        )->where(
            [
                // Return all between now and x amount of minutes
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :sec SECOND)')
            ]
        )->bind(':sec', $seconds, 'integer');
        // Only fetch and not fetchAll as result will be one row with the counts
        return $query->execute()->fetch('assoc');
    }

    /**
     * Retrieves info about request from a specific ip address
     *
     * @param string $ip
     * @param int $seconds
     * @return array[
     *     'request_amount' => int,
     *     'sent_emails' => int,
     *     'login_failures' => int
     *     'login_successes' => int
     *    ]
     */
    public function getIpRequestStats(string $ip, int $seconds): array
    {
        $query = $this->newSelectQuery();
        $query->select(
            [
                'request_amount' => $query->func()->count(1),
                'sent_emails' => $query->func()->sum('sent_email'),
                'login_failures' => $query->func()->sum('CASE WHEN is_login LIKE "failure" THEN 1 ELSE 0 END'),
                'login_successes' => $query->func()->sum('CASE WHEN is_login LIKE "success" THEN 1 ELSE 0 END'),
            ]
        )->where(
            [
                // Return all between now and x amount of minutes
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :sec SECOND)')
            ]
        )->andWhere(
            [
                'ip_address' => $query->newExpr("INET_ATON(:ip)")
            ]
        )->bind(':sec', $seconds, 'integer')->bind(':ip', $ip, 'string');

        // Only fetch and not fetchAll as result will be one row with the counts
        return $query->execute()->fetch('assoc');
    }

    /**
     * Retrieves info about request concerning a specific email address
     *
     * @param string $email
     * @param int $seconds
     * @return array[
     *     'request_amount' => int,
     *     'sent_emails' => int,
     *     'login_failures' => int
     *     'login_successes' => int
     * ]
     */
    public function getUserRequestStats(string $email, int $seconds): array
    {
        $query = $this->newSelectQuery();
        $query->select(
            [
                'request_amount' => $query->func()->count(1),
                'sent_emails' => $query->func()->sum('sent_email'),
                'login_failures' => $query->func()->sum('CASE WHEN is_login LIKE "failure" THEN 1 ELSE 0 END'),
                'login_successes' => $query->func()->sum('CASE WHEN is_login LIKE "success" THEN 1 ELSE 0 END'),
            ]
        )->where(
            [
                // Return all between now and x amount of minutes
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :sec SECOND)')
            ]
        )->andWhere(
            [
                'email' => $email
            ]
        )->bind(':sec', $seconds, 'integer');

        // Only fetch and not fetchAll as result will be one row with the counts
        return $query->execute()->fetch('assoc');
    }

    /**
     * Searches the latest failed request concerning a specific email address
     *
     * @param string $email
     * @param string $ip
     * @return array user data
     */
    public function findLatestLoginRequestFromUserOrIp(string $email, string $ip): array
    {
        $query = $this->newSelectQuery();
        $query->select('*')->where(
            [
                'is_login IS NOT' => null,
                'OR' => ['email' => $email, 'ip_address' => $query->newExpr("INET_ATON(:ip)")],
                // output: WHERE ((`is_login`) IS NOT NULL AND (`email` = :c0 OR `ip_address` = (INET_ATON(:ip))))
            ]
        )->bind(':ip', $ip, 'string')->orderDesc('created_at')->limit(1);
        return $query->execute()->fetch('assoc');
    }

    public function findLatestEmailRequestFromUserOrIp(string $email, string $ip): array
    {
        $query = $this->newSelectQuery();
        $query->select('*')->where(
            [
                'sent_email' => 1,
                'OR' => ['email' => $email, 'ip_address' => $query->newExpr("INET_ATON(:ip)")],
            ]
        )->bind(':ip', $ip, 'string')->orderDesc('created_at')->limit(1);
        return $query->execute()->fetch('assoc');
    }

    /**
     * Returns global login amount stats of all time
     *
     * @return array
     */
    public function getLoginAmountStats(): array
    {
        $query = $this->newSelectQuery();
        $query->select(
            [
                'login_total' => $query->func()->count(1),
                'login_failures' => $query->func()->sum('CASE WHEN is_login LIKE "failure" THEN 1 ELSE 0 END'),
            ]
        )->where(
            [
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL 1 MONTH)')
            ]
        );
        return $query->execute()->fetch('assoc');
    }

    /**
     * Gives sent email amount globally in the last given amount of days
     *
     * @param int $days
     * @return string sent email amount
     */
    public function getGlobalSentEmailAmount(int $days): string
    {
        $query = $this->newSelectQuery();
        $query->select(['sent_email_amount' => $query->func()->sum('sent_email')])->where(
            [
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :days DAY)')
            ]
        )->bind(':days', $days, 'integer');
        return $query->execute()->fetch('assoc')['sent_email_amount'] ?? '0';
    }


}
