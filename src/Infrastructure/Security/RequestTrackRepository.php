<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Common\Hydrator;
use App\Infrastructure\DataManager;

class RequestTrackRepository
{

    public function __construct(private DataManager $dataManager, private Hydrator $hydrator)
    {
    }

    /**
     * Insert new request where an email was sent
     *
     * @param string $email
     * @param string $ip
     * @return string
     */
    public function insertEmailRequest(string $email, string $ip): string
    {
        $query = $this->dataManager->newQuery();

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
        $query = $this->dataManager->newQuery();
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
        $query = $this->dataManager->newQuery();
        $query->select(
            [
                'request_amount' => $query->func()->count(1),
                'sent_emails' => $query->func()->sum('sent_email'),
                'login_failures' => $query->func()->sum('CASE WHEN is_login LIKE "failure" THEN 1 ELSE 0 END'),
                'login_successes' => $query->func()->sum('CASE WHEN is_login LIKE "success" THEN 1 ELSE 0 END'),
            ]
        )->from('request_track')->where(
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
        $query = $this->dataManager->newQuery();
        $query->select(
            [
                'request_amount' => $query->func()->count(1),
                'sent_emails' => $query->func()->sum('sent_email'),
                'login_failures' => $query->func()->sum('CASE WHEN is_login LIKE "failure" THEN 1 ELSE 0 END'),
                'login_successes' => $query->func()->sum('CASE WHEN is_login LIKE "success" THEN 1 ELSE 0 END'),
            ]
        )->from('request_track')->where(
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
        $query = $this->dataManager->newQuery();
        $query->select(
            [
                'request_amount' => $query->func()->count(1),
                'sent_emails' => $query->func()->sum('sent_email'),
                'login_failures' => $query->func()->sum('CASE WHEN is_login LIKE "failure" THEN 1 ELSE 0 END'),
                'login_successes' => $query->func()->sum('CASE WHEN is_login LIKE "success" THEN 1 ELSE 0 END'),
            ]
        )->from('request_track')->where(
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
        $query = $this->dataManager->newQuery();
        $query->select('*')->from('request_track')->where(
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
        $query = $this->dataManager->newQuery();
        $query->select('*')->from('request_track')->where(
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
    public function getGlobalLoginAmountStats(): array
    {
        $query = $this->dataManager->newQuery();
        $query->select(
            [
                'login_total' => $query->func()->count(1),
                'login_failures' => $query->func()->sum('CASE WHEN is_login LIKE "failure" THEN 1 ELSE 0 END'),
            ]
        )->from('request_track')->where(
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
        $query = $this->dataManager->newQuery();
        $query->select(['sent_email_amount' => $query->func()->sum('sent_email')])->from('request_track')->where(
            [
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :days DAY)')
            ]
        )->bind(':days', $days, 'integer');
        return $query->execute()->fetch('assoc')['sent_email_amount'] ?? '0';
    }

    /**
     * Set the created_at time to x amount of seconds earlier
     * Used in testing to simulate waiting delay
     *
     * @param int $seconds
     * @return bool
     */
    public function preponeLastRequest(int $seconds): bool
    {
        $query = $this->dataManager->newQuery();
        $query->update('request_track')->set(
            [
                'created_at' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :sec SECOND)')
            ]
        )->orderDesc('id')->limit(1)->bind(':sec', $seconds, 'integer');
        return $query->execute()->rowCount() > 0;
    }

}