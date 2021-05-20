<?php


namespace App\Domain\Security\Service;


use App\Domain\Security\DTO\RequestData;
use App\Domain\Security\DTO\RequestStatsData;
use App\Domain\Settings;
use App\Infrastructure\Security\RequestFinderRepository;

/**
 * This class seems like it doesn't contain logic but does! It loads the settings and retrieves the ip address
 * Additionally, this class treats UserStatsData and normal RequestData this is because they are used in the same
 * use case.
 */
class SecurityRequestFinder
{
    private array $securitySettings;

    public function __construct(
        private RequestFinderRepository $requestStatsFinderRepository,
        private RequestFinderRepository $requestFinderRepository,
        Settings $settings
    ) {
        $this->securitySettings = $settings->get('security');
    }

    /**
     * Retrieves request summary from the actual ip address
     *
     * @return RequestStatsData ip requests
     */
    public function retrieveIpStats(): RequestStatsData
    {
        // This service should be called when retrieving ip stats as this class loads the settings it
        return $this->requestStatsFinderRepository->getIpRequestStats(
            $_SERVER['REMOTE_ADDR'],
            $this->securitySettings['timespan']
        );
    }

    /**
     * @param string $email
     * @return RequestStatsData
     */
    public function retrieveUserStats(string $email): RequestStatsData
    {
        // This service should be called when retrieving ip stats as this class loads the settings it
        // Stats concerning given email in last timespan
        return $this->requestStatsFinderRepository->getUserRequestStats($email, $this->securitySettings['timespan']);
    }

    /**
     * Returns the very last LOGIN request from actual ip or given email
     *
     * @param string $email
     * @return RequestData
     */
    public function findLatestLoginRequestFromUserOrIp(string $email): RequestData
    {
        return $this->requestFinderRepository->findLatestLoginRequestFromUserOrIp(
            $email,
            $_SERVER['REMOTE_ADDR']
        );
    }

    /**
     * Returns the very last EMAIL request from actual ip or given email
     *
     * @param string $email
     * @return RequestData
     */
    public function findLatestEmailRequestFromUserOrIp(string $email): RequestData
    {
        return $this->requestFinderRepository->findLatestEmailRequestFromUserOrIp(
            $email,
            $_SERVER['REMOTE_ADDR']
        );
    }
}