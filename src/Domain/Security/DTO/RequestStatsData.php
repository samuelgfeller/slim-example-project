<?php


namespace App\Domain\Security\DTO;

use App\Domain\Utility\ArrayReader;

/**
 * Summary of requests
 */
class RequestStatsData
{
    public int $sentEmails;
    public int $loginFailures;
    public int $loginSuccesses;

    public function __construct(array $data = []) {
        $reader = new ArrayReader($data);
        $this->sentEmails = $reader->getInt('sent_emails');
        $this->loginFailures = $reader->getInt('login_failures');
        $this->loginSuccesses = $reader->getInt('login_successes');
    }
}