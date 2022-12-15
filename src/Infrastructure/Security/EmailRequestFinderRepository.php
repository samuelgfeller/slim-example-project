<?php

namespace App\Infrastructure\Security;

use App\Domain\Security\Data\RequestData;
use App\Domain\Security\Data\RequestStatsData;
use App\Infrastructure\Factory\QueryFactory;

class EmailRequestFinderRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * @param string $email
     * @param string $ip
     * @param int $seconds
     *
     * @return array
     */
    public function getEmailRequestStatsFromEmailAndIp(string $email, string $ip, int $seconds): array
    {
        $stats = ['email_stats' => [], 'ip_stats' => []];

        // Only return values if not empty string as it doesn't represent a user request
        if ($email !== '') {
            $stats['email_stats'] = $this->getEmailRequestStats(['email' => $email], $seconds);
        }
        if ($ip !== '') {
            $stats['ip_stats'] = $this->getEmailRequestStats(
                ['ip_address' => $this->queryFactory->newQuery()->newExpr("INET_ATON('$ip')")],
                $seconds
            );
        }

        return $stats;
    }

    /**
     * Retrieves request summary from the given ip address.
     *
     * @param array $whereEmailOrIpArr
     * @param int $seconds
     * Throws PersistenceRecordNotFoundException if entry not found
     *
     * @return RequestStatsData
     */
    private function getEmailRequestStats(array $whereEmailOrIpArr, int $seconds): RequestStatsData
    {
        $query = $this->queryFactory->newQuery();
        $query->select(
            [
                'sent_emails' => $query->func()->sum('sent_email'),
            ]
        )->from('user_request')->where(
            [
                // Return all between now and x amount of minutes
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :sec SECOND)'),
            ]
        )->andWhere($whereEmailOrIpArr)->bind(':sec', $seconds, 'integer');

        // Only fetch and not fetchAll as result will be one row with the counts
        $requestStats = $query->execute()->fetch('assoc');

        return new RequestStatsData($requestStats);
    }

    /**
     * Searches the latest email request concerning a specific email address.
     *
     * @param string $email
     * @param string $ip
     *
     * @return RequestData
     */
    public function findLatestEmailRequestFromUserOrIp(string $email, string $ip): RequestData
    {
        $query = $this->queryFactory->newQuery();
        $query->select('*')->from('user_request')->where(
            [
                'sent_email' => 1,
                'OR' => ['email' => $email, 'ip_address' => $query->newExpr('INET_ATON(:ip)')],
            ]
        )->bind(':ip', $ip, 'string')->orderDesc('created_at')->limit(1);

        return new RequestData($query->execute()->fetch('assoc') ?: []);
    }

    /**
     * Gives sent email amount globally in the last given amount of days.
     *
     * @param int $days
     *
     * @return int sent email amount
     */
    public function getGlobalSentEmailAmount(int $days): int
    {
        $query = $this->queryFactory->newQuery();
        $query->select(
            [
                'sent_email_amount' => $query->func()->sum('sent_email'),
            ]
        )->from('user_request')->where(
            [
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :days DAY)'),
            ]
        )->bind(':days', $days, 'integer');
        $val = (int)($query->execute()->fetch('assoc')['sent_email_amount'] ?? 0);

        return $val;
    }
}
