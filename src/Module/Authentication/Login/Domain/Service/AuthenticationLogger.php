<?php

namespace App\Module\Authentication\Login\Domain\Service;

use App\Core\Application\Data\UserNetworkSessionData;
use App\Module\Authentication\Login\Repository\AuthenticationLoggerRepository;

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
