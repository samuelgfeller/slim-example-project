<?php


namespace App\Domain\Security\DTO;

use App\Common\ArrayReader;

/**
 * Summary of requests
 */
class RequestStatsData
{
    // May be null when there are no requests
    public ?int $sentEmails;
    public ?int $loginFailures;
    public ?int $loginSuccesses;

    /**
     * RequestStatsData constructor.
     * @param array $data
     */
    public function __construct(array $data = []) {
        $reader = new ArrayReader($data);
        $this->sentEmails = $reader->findInt('sent_emails');
        $this->loginFailures = $reader->findInt('login_failures');
        $this->loginSuccesses = $reader->findInt('login_successes');
    }
}