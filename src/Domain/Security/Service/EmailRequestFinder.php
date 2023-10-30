<?php

namespace App\Domain\Security\Service;

use App\Domain\Security\Repository\EmailLogFinderRepository;
use App\Domain\Settings;

class EmailRequestFinder
{
    private array $securitySettings;

    public function __construct(
        private readonly EmailLogFinderRepository $emailRequestFinderRepository,
        Settings $settings
    ) {
        $this->securitySettings = $settings->get('security');
    }

    /**
     * Retrieve email requests from given email and client ip.
     *
     * @param string $email
     *
     * @return int
     */
    public function findEmailAmountInSetTimespan(string $email): int
    {
        // This service should be called when retrieving ip stats as this class loads the settings it
        // Stats concerning given email in last timespan
        return $this->emailRequestFinderRepository->getLoggedEmailCountInTimespan(
            $email,
            $this->securitySettings['timespan']
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
        if ($createdAt) {
            return (new \DateTime($createdAt))->format('U');
        }

        return 0;
    }
}
