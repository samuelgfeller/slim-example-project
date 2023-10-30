<?php

namespace App\Domain\Authentication\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Domain\Authentication\Exception\InvalidCredentialsException;
use App\Domain\Security\Repository\AuthenticationLoggerRepository;
use App\Domain\Security\Service\SecurityLoginChecker;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Repository\UserFinderRepository;
use App\Domain\User\Service\UserValidator;
use App\Domain\UserActivity\Service\UserActivityLogger;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class LoginVerifier
{
    public function __construct(
        private readonly UserValidator $userValidator,
        private readonly SecurityLoginChecker $loginSecurityChecker,
        private readonly UserFinderRepository $userFinderRepository,
        private readonly AuthenticationLoggerRepository $authenticationLoggerRepository,
        private readonly LoginNonActiveUserHandler $loginNonActiveUserHandler,
        private readonly UserActivityLogger $userActivityLogger,
        private readonly UserNetworkSessionData $ipAddressData,
    ) {
    }

    /**
     * Checks if user is allowed to login.
     * If yes, the user object is returned with id
     * If no, an InvalidCredentialsException is thrown.
     *
     * @param array $userLoginValues
     * @param string|null $captcha user captcha response if filled out
     * @param array $queryParams
     *
     * @throws TransportExceptionInterface
     *
     * @return int id
     */
    public function getUserIdIfAllowedToLogin(
        array $userLoginValues,
        ?string $captcha = null,
        array $queryParams = []
    ): int {
        // Validate entries coming from client
        $this->userValidator->validateUserLogin($userLoginValues);

        // Perform login security check
        $this->loginSecurityChecker->performLoginSecurityCheck($userLoginValues['email'], $captcha);

        $dbUser = $this->userFinderRepository->findUserByEmail($userLoginValues['email']);
        // Check if user exists
        // Verify if password matches and enter login request
        if (($dbUser->email !== null) && password_verify($userLoginValues['password'], $dbUser->passwordHash)) {
            // If password correct and status active, log user in by
            if ($dbUser->status === UserStatus::Active) {
                // Insert login success request
                $this->authenticationLoggerRepository->logLoginRequest(
                    $dbUser->email,
                    $this->ipAddressData->ipAddress,
                    true,
                    $dbUser->id
                );

                $this->userActivityLogger->logUserActivity(
                    UserActivity::READ,
                    'user',
                    $dbUser->id,
                    ['login'],
                    $dbUser->id
                );

                // Return id (not sure if it's better to regenerate session here in service or in action)
                return $dbUser->id;
            }

            // If password is correct but the status not verified, send email to user
            // captcha needed if email security check requires captcha
            $this->loginNonActiveUserHandler->handleLoginAttemptFromNonActiveUser($dbUser, $queryParams, $captcha);
        }
        // Password not correct or user not existing - insert login request for ip
        $this->authenticationLoggerRepository->logLoginRequest(
            $userLoginValues['email'],
            $this->ipAddressData->ipAddress,
            false,
            $dbUser->id
        );

        // Perform second login request check to display the correct error message to the user if throttle is in place
        $this->loginSecurityChecker->performLoginSecurityCheck($userLoginValues['email'], $captcha);

        // Throw InvalidCred exception if user doesn't exist or wrong password
        // Vague exception on purpose for security
        throw new InvalidCredentialsException($userLoginValues['email']);
    }
}
