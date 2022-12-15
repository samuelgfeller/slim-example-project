<?php

namespace App\Domain\Security\Data;

/**
 * Summary of requests.
 */
class RequestStatsData
{
    // May be null when there are no requests
    public ?int $sentEmails;
    public ?int $loginFailures;
    public ?int $loginSuccesses;

    /**
     * RequestStatsData constructor.
     *
     * @param array $requestStatsData
     */
    public function __construct(array $requestStatsData = [])
    {
        $this->sentEmails = $requestStatsData['sent_emails'] ?? null;
        $this->loginFailures = $requestStatsData['login_failures'] ?? null;
        $this->loginSuccesses = $requestStatsData['login_successes'] ?? null;
    }
}
