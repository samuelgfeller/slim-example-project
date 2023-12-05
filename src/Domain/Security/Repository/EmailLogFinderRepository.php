<?php

namespace App\Domain\Security\Repository;

use App\Domain\Factory\Infrastructure\QueryFactory;

readonly class EmailLogFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory
    ) {
    }

    /**
     * Retrieves request summary from the given ip address.
     *
     * @param string $email
     * @param int $seconds
     * Throws PersistenceRecordNotFoundException if entry not found
     *
     * @return int
     */
    public function getLoggedEmailCountInTimespan(string $email, int $seconds): int
    {
        $query = $this->queryFactory->selectQuery();
        $query->select(
            [
                'email_amount' => $query->func()->count('id'),
            ]
        )->from('email_log')->where(
            [
                // Return all between now and x amount of minutes
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :sec SECOND)'),
                'to_email' => $email,
            ]
        )->bind(':sec', $seconds, 'integer');

        // Only fetch and not fetchAll as result will be one row with the counts
        $result = $query->execute()->fetch('assoc');

        return (int)$result['email_amount'];
    }

    /**
     * Searches the latest email request concerning a specific email address.
     *
     * @param string $email
     *
     * @return string|bool datetime of last email request or false
     */
    public function findLatestEmailRequest(string $email): bool|string
    {
        $query = $this->queryFactory->selectQuery();
        $query->select('created_at')->from('email_log')->where(
            [
                'to_email' => $email,
            ]
        )->orderByDesc('created_at')->limit(1);

        return $query->execute()->fetch('assoc')['created_at'] ?: false;
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
        $query = $this->queryFactory->selectQuery();
        $query->select(
            [
                'sent_email_amount' => $query->func()->count('id'),
            ]
        )->from('email_log')->where(
            [
                'created_at >' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :days DAY)'),
            ]
        )->bind(':days', $days, 'integer');

        return (int)($query->execute()->fetch('assoc')['sent_email_amount'] ?? 0);
    }
}
