<?php

namespace App\Application\Data;

/**
 * DTO that contains client's network data such as
 * IP address and user agent and user identity id.
 */
class UserNetworkSessionData
{
    // Initialize vars with default values
    public ?string $ipAddress = null;
    public ?string $userAgent = null;
    public int $userId;
}
