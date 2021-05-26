<?php


namespace App\Infrastructure\Security;


use App\Domain\Security\DTO\RequestData;
use App\Domain\Security\DTO\RequestStatsData;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use App\Infrastructure\Factory\QueryFactory;
use JetBrains\PhpStorm\ArrayShape;

class RequestFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory
    ) {
    }

    /**
     * Retrieves request summary from the given ip address
     *
     * @param string $ip
     * @param int $seconds
     * Throws PersistenceRecordNotFoundException if entry not found
     * @return RequestStatsData
     */
    public function getIpRequestStats(string $ip, int $seconds): RequestStatsData
    {
        $query = $this->queryFactory->newQuery();
        $query->select(
            [
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
        $requestStats = $query->execute()->fetch('assoc');
        if (!$requestStats){
            throw new PersistenceRecordNotFoundException('requestStats');
        }
        return new RequestStatsData($requestStats);
    }

    /**
     * Retrieves info about request concerning a specific email address
     *
     * @param string $email
     * @param int $seconds
     * @return RequestStatsData
     */
    public function getUserRequestStats(string $email, int $seconds): RequestStatsData
    {
        $query = $this->queryFactory->newQuery();
        $query->select(
            [
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
        return new RequestStatsData($query->execute()->fetch('assoc'));
    }

    /**
     * Searches the latest failed login request concerning a specific email address
     *
     * @param string $email
     * @param string $ip
     * @return RequestData
     */
    public function findLatestLoginRequestFromUserOrIp(string $email, string $ip): RequestData
    {
        $query = $this->queryFactory->newQuery();
        $query->select('*')->from('request_track')->where(
            [
                'is_login IS NOT' => null,
                'OR' => ['email' => $email, 'ip_address' => $query->newExpr("INET_ATON(:ip)")],
                // output: WHERE ((`is_login`) IS NOT NULL AND (`email` = :c0 OR `ip_address` = (INET_ATON(:ip))))
            ]
        )->bind(':ip', $ip, 'string')->orderDesc('created_at')->limit(1);
        return new RequestData($query->execute()->fetch('assoc') ?: []);
    }

    /**
     * Searches the latest email request concerning a specific email address
     *
     * @param string $email
     * @param string $ip
     * @return RequestData
     */
    public function findLatestEmailRequestFromUserOrIp(string $email, string $ip): RequestData
    {
        $query = $this->queryFactory->newQuery();
        $query->select('*')->from('request_track')->where(
            [
                'sent_email' => 1,
                'OR' => ['email' => $email, 'ip_address' => $query->newExpr("INET_ATON(:ip)")],
            ]
        )->bind(':ip', $ip, 'string')->orderDesc('created_at')->limit(1);
        return new RequestData($query->execute()->fetch('assoc') ?: []);
    }

    /**
     * Returns global login amount stats of all time
     *
     * @return array<array>
     */
    #[ArrayShape(['login_total' => "array", 'login_failures' => "array"])]
    public function getGlobalLoginAmountStats(): array
    {
        $query = $this->queryFactory->newQuery();
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
     * @return int sent email amount
     */
    public function getGlobalSentEmailAmount(int $days): int
    {
        $query = $this->queryFactory->newQuery();
        $query->select(
            [
                'sent_email_amount' => $query->func()->sum('sent_email')
            ]
        )->from('request_track')->where(
            [
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :days DAY)')
            ]
        )->bind(':days', $days, 'integer');
        $val = (int)($query->execute()->fetch('assoc')['sent_email_amount'] ?? 0);
        return $val;
    }
}