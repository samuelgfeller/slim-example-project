<?php

namespace App\Infrastructure\Security;

use App\Domain\Security\Data\RequestData;
use App\Domain\Security\Data\RequestStatsData;
use App\Infrastructure\Factory\QueryFactory;

class LoginRequestFinderRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Retrieves request summary from the given ip address.
     *
     * @param string $email
     * @param string $ip
     * @param int $seconds
     *
     * @return array{email_stats: RequestStatsData, ip_stats: RequestStatsData}
     */
    public function getLoginRequestStatsFromEmailAndIp(string $email, string $ip, int $seconds): array
    {
        $stats = ['email_stats' => [], 'ip_stats' => []];

        // Only return values if not empty string as it doesn't represent a user request
        if ($email !== '') {
            $stats['email_stats'] = $this->getLoginRequestStats(['email' => $email], $seconds);
        }
        if ($ip !== '') {
            $stats['ip_stats'] = $this->getLoginRequestStats(
                ['ip_address' => $this->queryFactory->newQuery()->newExpr("INET_ATON('$ip')")],
                $seconds
            );
        }

        return $stats;
    }

    /**
     * Retrieves info about request concerning a specific email address.
     *
     * @param array $whereEmailOrIpArr
     * @param int $seconds
     *
     * @return RequestStatsData
     */
    private function getLoginRequestStats(array $whereEmailOrIpArr, int $seconds): RequestStatsData
    {
        $query = $this->queryFactory->newQuery();
        $query->select(
            [
                'login_failures' => $query->func()->sum('CASE WHEN is_login LIKE "failure" THEN 1 ELSE 0 END'),
                'login_successes' => $query->func()->sum('CASE WHEN is_login LIKE "success" THEN 1 ELSE 0 END'),
            ]
        )->from('user_request')->where(
            [
                // Return all between now and x amount of minutes
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :sec SECOND)'),
                'is_login IS NOT' => null,
            ]
        )->andWhere($whereEmailOrIpArr)->bind(':sec', $seconds, 'integer');
        $sql = $query->sql();
        // Only fetch and not fetchAll as result will be one row with the counts
        return new RequestStatsData($query->execute()->fetch('assoc'));
    }

    /**
     * Searches the latest failed login request concerning a specific email address.
     *
     * @param string $email
     * @param string $ip
     *
     * @return RequestData
     */
    public function findLatestLoginRequestFromUserOrIp(string $email, string $ip): RequestData
    {
        $query = $this->queryFactory->newQuery();
        $query->select('*')->from('user_request')->where(
            [
                'is_login IS NOT' => null,
                'OR' => ['email' => $email, 'ip_address' => $query->newExpr('INET_ATON(:ip)')],
                // output: WHERE ((`is_login`) IS NOT NULL AND (`email` = :c0 OR `ip_address` = (INET_ATON(:ip))))
            ]
            // Order desc id instead of created at for testing as last request is preponed to simulate waiting
        )->bind(':ip', $ip, 'string')->orderDesc('id')->limit(1);

        return new RequestData($query->execute()->fetch('assoc') ?: []);
    }

    /**
     * Returns global login amount stats of last day.
     *
     * @return array{
     *     login_total: array,
     *     login_failures: array
     * }
     */
    public function getGlobalLoginAmountStats(): array
    {
        $query = $this->queryFactory->newQuery();
        $query->select(
            [
                'login_total' => $query->func()->count(1),
                'login_failures' => $query->func()->sum('IF(is_login LIKE "failure", 1, 0)'),
            ]
        )->from('user_request')->where(
            [
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL 1 MONTH)'),
                'is_login IS NOT' => null,
            ]
        );

        return $query->execute()->fetch('assoc');
    }
}
