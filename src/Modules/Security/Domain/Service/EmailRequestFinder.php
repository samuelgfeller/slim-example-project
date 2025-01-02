<?php

namespace App\Modules\Security\Domain\Service;

use App\Core\Infrastructure\Utility\Settings;
use App\Modules\Security\Repository\EmailLogFinderRepository;

class EmailRequestFinder
{
    private array $securitySettings;

    public function __construct(
        private readonly EmailLogFinderRepository $emailRequestFinderRepository,
        Settings $settings,
    ) {
        $this->securitySettings = $settings->get('security');
    }

    /**
     * Retrieve email requests from given email or user.
     *
     * @param string $email
     * @param int|null $userId
     *
     * @return int
     */
    public function findEmailAmountInSetTimespan(string $email, ?int $userId): int
    {
        // This service should be called when retrieving ip stats as this class loads the settings it
        // Stats concerning given email in last timespan
        return $this->emailRequestFinderRepository->getLoggedEmailCountInTimespan(
            $email,
            $this->securitySettings['timespan'],
            $userId
        );
    }

    /**
     * Returns the very last EMAIL request from actual ip or given email.
     *
     * @param string $email
     *
     * @return int
     */
    public function findLastEmailRequestTimestamp(string $email): int
    {
        $createdAt = $this->emailRequestFinderRepository->findLatestEmailRequest($email);
        if (is_string($createdAt)) {
            return (int)(new \DateTime($createdAt))->format('U');
        }

        return 0;
    }
}
