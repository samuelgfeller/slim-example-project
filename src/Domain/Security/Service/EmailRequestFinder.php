<?php

namespace App\Domain\Security\Service;

use App\Domain\Security\Data\RequestData;
use App\Domain\Security\Data\RequestStatsData;
use App\Domain\Settings;
use App\Infrastructure\Security\EmailRequestFinderRepository;

class EmailRequestFinder
{
    private array $securitySettings;

    public function __construct(
        private readonly EmailRequestFinderRepository $emailRequestFinderRepository,
        Settings $settings
    ) {
        $this->securitySettings = $settings->get('security');
    }

    /**
     * Retrieve email requests from given email and client ip.
     *
     * @param string $email
     *
     * @return array{email_stats: RequestStatsData, ip_stats: RequestStatsData}
     */
    public function findEmailStats(string $email): array
    {
        // This service should be called when retrieving ip stats as this class loads the settings it
        // Stats concerning given email in last timespan
        return $this->emailRequestFinderRepository->getEmailRequestStatsFromEmailAndIp(
            $email,
            $_SERVER['REMOTE_ADDR'],
            $this->securitySettings['timespan']
        );
    }

    /**
     * Returns the very last EMAIL request from actual ip or given email.
     *
     * @param string $email
     *
     * @return RequestData
     */
    public function findLatestEmailRequestFromUserOrIp(string $email): RequestData
    {
        return $this->emailRequestFinderRepository->findLatestEmailRequestFromUserOrIp(
            $email,
            $_SERVER['REMOTE_ADDR']
        );
    }
}
