<?php

namespace App\Module\Authentication\Login\Domain\Service;

use App\Module\Authentication\Login\Domain\Exception\InvalidCredentialsException;
use App\Module\Authentication\Validation\AuthenticationValidator;
use App\Module\Security\Domain\Service\SecurityLoginChecker;
use App\Module\User\Enum\UserActivity;
use App\Module\User\Enum\UserStatus;
use App\Module\User\Repository\UserFinderRepository;
use App\Module\UserActivity\Service\UserActivityLogger;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final readonly class LoginVerifier
{
    public function __construct(
        private AuthenticationValidator $authenticationValidator,
        private SecurityLoginChecker $loginSecurityChecker,
        private UserFinderRepository $userFinderRepository,
        private LoginNonActiveUserHandler $loginNonActiveUserHandler,
        private UserActivityLogger $userActivityLogger,
        private AuthenticationLogger $authenticationLogger,
    ) {
    }

    /**
     * Verifies the user's login credentials and returns the user id if the login is successful.
     *
     * @param array $userLoginValues An associative array containing the user's login credentials.
     * Expected keys are 'email' and 'password' and optionally 'g-recaptcha-response'.
     * @param array $queryParams an associative array containing any additional query parameters
     *
     * @throws TransportExceptionInterface if an error occurs while sending an email to a non-active user
     * @throws InvalidCredentialsException if the user does not exist or the password is incorrect
     *
     * @return int the ID of the user if the login is successful
     */
    public function verifyLoginAndGetUserId(array $userLoginValues, array $queryParams = []): int
    {
        // Validate submitted values
        $this->authenticationValidator->validateUserLogin($userLoginValues);
        $captcha = $userLoginValues['g-recaptcha-response'] ?? null;

        // Perform login security check before verifying credentials
        $this->loginSecurityChecker->performLoginSecurityCheck($userLoginValues['email'], $captcha);

        $dbUser = $this->userFinderRepository->findUserByEmail($userLoginValues['email']);

        // Check if the user exists and check if the password is correct
        if (isset($dbUser->email, $dbUser->passwordHash)
            && password_verify($userLoginValues['password'], $dbUser->passwordHash)) {
            // If password correct and status active, return user id to log user in
            if ($dbUser->status === UserStatus::Active) {
                // Log successful login request
                $this->authenticationLogger->logLoginRequest($dbUser->email, true, $dbUser->id);

                $this->userActivityLogger->logUserActivity(
                    UserActivity::READ,
                    'user',
                    $dbUser->id,
                    ['login'],
                    $dbUser->id
                );

                // Return id
                return (int)$dbUser->id;
            }

            // If the password is correct but the status not verified, send email to user
            // captcha needed if email security check requires captcha
            $this->loginNonActiveUserHandler->handleLoginAttemptFromNonActiveUser($dbUser, $queryParams, $captcha);
        }
        // Password is not correct or user not existing
        // Log failed login request
        $this->authenticationLogger->logLoginRequest($userLoginValues['email'], false, $dbUser->id);

        // Perform second login security request check after additional verification to display
        // the correct error message to the user if throttle is in place
        $this->loginSecurityChecker->performLoginSecurityCheck($userLoginValues['email'], $captcha);

        // Throw exception if the user doesn't exist or wrong password
        // Vague exception on purpose in favor of security
        throw new InvalidCredentialsException($userLoginValues['email']);
    }
}
