<?php

namespace App\Infrastructure\SecurityLogging;

use App\Infrastructure\Factory\QueryFactory;

class LoginLogFinderRepository
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
     * @return array{
     *     logins_by_email: array{successes: int, failures: int},
     *     logins_by_ip: array{successes: int, failures: int},
     * }
     */
    public function getLoginSummaryFromEmailAndIp(string $email, string $ip, int $seconds): array
    {
        $summary = ['logins_by_ip' => [], 'logins_by_email' => []];

        // Only return val34ues if not empty string as it doesn't represent a user request
        if ($email !== '') {
            $summary['logins_by_email'] = $this->getLoginRequestStats(['email' => $email], $seconds);
        }
        if ($ip !== '') {
            $summary['logins_by_ip'] = $this->getLoginRequestStats(['ip_address' => $ip], $seconds);
        }

        return $summary;
    }

    /**
     * Retrieves info about request concerning a specific email address.
     *
     * @param array $whereEmailOrIpArr
     * @param int $seconds
     *
     * @return array{successes: int, failures: int}
     */
    private function getLoginRequestStats(array $whereEmailOrIpArr, int $seconds): array
    {
        $query = $this->queryFactory->newQuery();
        $query->select(
            [
                'login_successes' => $query->func()->sum('is_success'),
                'total_logins' => $query->func()->count('id'),
            ]
        )->from('authentication_log')->where(
            [
                // Return all between now and x amount of minutes
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :sec SECOND)'),
            ]
        )->andWhere($whereEmailOrIpArr)->bind(':sec', $seconds, 'integer');
        // $sql = $query->sql();
        // Only fetch and not fetchAll as result will be one row with the counts
        $result = $query->execute()->fetch('assoc');
        return [
            'successes' => (int)$result['login_successes'],
            'failures' => (int)$result['total_logins'] - (int)$result['login_successes']
        ];
    }

    /**
     * Searches the latest failed login request concerning a specific email address.
     *
     * @param string $email
     * @param string $ip
     *
     * @return string
     */
    public function findLatestLoginTimestampFromUserOrIp(string $email, string $ip): string
    {
        $query = $this->queryFactory->newQuery();
        $query->select('created_at')->from('authentication_log')->where(
            [
                'OR' => ['email' => $email, 'ip_address' => $ip],
            ]
            // Order desc id instead of created at for testing as last request is preponed to simulate waiting
        )->orderDesc('id')->limit(1);

        return $query->execute()->fetch('assoc')['created_at'] ?? 0;
    }

    /**
     * Returns global login amount stats of last day.
     *
     * @return array{
     *     total_amount: int,
     *     successes: int,
     *     failures: int
     * }
     */
    public function getGlobalLoginAmountSummary(): array
    {
        $query = $this->queryFactory->newQuery();
        $query->select(
            [
                'total_amount' => $query->func()->count(1),
                'successes' => $query->func()->sum('is_success'),
            ]
        )->from('authentication_log')->where(
            [
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL 1 MONTH)'),
            ]
        );

        $result = $query->execute()->fetch('assoc');
        return [
            'total_amount' => (int)$result['total_amount'],
            'successes' => (int)$result['successes'],
            'failures' => (int)$result['total_amount'] - (int)$result['successes']
        ];
    }
}
