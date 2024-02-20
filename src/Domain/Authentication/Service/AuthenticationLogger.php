<?php

namespace App\Domain\Authentication\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Domain\Security\Repository\AuthenticationLoggerRepository;

final readonly class AuthenticationLogger
{
    public function __construct(
        private UserNetworkSessionData $userNetworkSessionData,
        private AuthenticationLoggerRepository $authenticationLoggerRepository,
    ) {
    }

    /**
     * Log login request.
     *
     * @param string $email
     * @param bool $success whether login request was a successful login or not
     * @param int|null $userId
     *
     * @return int
     */
    public function logLoginRequest(string $email, bool $success, ?int $userId = null): int
    {
        return $this->authenticationLoggerRepository->logLoginRequest(
            $email,
            $this->userNetworkSessionData->ipAddress,
            $success,
            $userId
        );
    }
}
