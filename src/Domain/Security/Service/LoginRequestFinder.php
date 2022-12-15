<?php

namespace App\Domain\Security\Service;

use App\Domain\Security\Data\RequestData;
use App\Domain\Security\Data\RequestStatsData;
use App\Domain\Settings;
use App\Infrastructure\Security\LoginRequestFinderRepository;

class LoginRequestFinder
{
    private array $securitySettings;

    public function __construct(
        private readonly LoginRequestFinderRepository $loginRequestFinderRepository,
        Settings $settings
    ) {
        $this->securitySettings = $settings->get('security');
    }

    /**
     * Retrieve login requests from given email and client ip.
     *
     * @param string $email
     *
     * @return array{email_stats: RequestStatsData, ip_stats: RequestStatsData}
     */
    public function findLoginStats(string $email): array
    {
        // This service should be called when retrieving ip stats as this class loads the settings it
        // Stats concerning given email in last timespan
        return $this->loginRequestFinderRepository->getLoginRequestStatsFromEmailAndIp(
            $email,
            $_SERVER['REMOTE_ADDR'],
            $this->securitySettings['timespan']
        );
    }

    /**
     * Returns the very last LOGIN request from actual ip or given email.
     *
     * @param string $email
     *
     * @return RequestData
     */
    public function findLatestLoginRequestFromEmailOrIp(string $email): RequestData
    {
        return $this->loginRequestFinderRepository->findLatestLoginRequestFromUserOrIp(
            $email,
            $_SERVER['REMOTE_ADDR']
        );
    }
}
