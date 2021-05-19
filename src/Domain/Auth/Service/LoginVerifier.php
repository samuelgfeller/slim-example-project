<?php


namespace App\Domain\Auth\Service;


use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\User\User;
use App\Domain\User\UserValidator;

class LoginVerifier
{

    public function __construct(
        private UserValidator $userValidator,
    ) { }

    /**
     * Checks if user is allowed to login.
     * If yes, the user object is returned with id
     * If no, an InvalidCredentialsException is thrown
     *
     * @param array $userData
     * @param string|null $captcha user captcha response if filled out
     * @return string id
     *
     */
    public function getUserIdIfAllowedToLogin(array $userData, string|null $captcha = null): string
    {
        $user = new User($userData, true);

        // Validate entries coming from client
        $this->userValidator->validateUserLogin($user);

        // Perform login security check
        $this->securityService->performLoginSecurityCheck($user->email, $captcha);

        $dbUser = $this->userRepository->findUserByEmail($user->email);
        // Check if user already exists
        if ($dbUser->email !== null) {
            if ($dbUser->status === User::STATUS_UNVERIFIED) {
                // todo inform user when he tries to login that account is unverified and he should click on the link in his inbox
                // maybe send verification email again and newEmailRequest (not login as its same as register)
            } elseif ($dbUser->status === User::STATUS_SUSPENDED) {
                // Todo inform user (only via mail) that he is suspended and isn't allowed to create a new account
            } elseif ($dbUser->status === User::STATUS_LOCKED) {
                // Todo login fail and inform user (only via mail) that he is locked
            } elseif ($dbUser->status === User::STATUS_ACTIVE) {
                // Check failed login attempts
                if (password_verify($user->password, $dbUser->passwordHash)) {
                    $this->securityService->newLoginRequest($dbUser->email, $_SERVER['REMOTE_ADDR'], true);
                    return $dbUser->id;
                }
            } else {
                // todo invalid role in db. Send email to admin to inform that there is something wrong with the user
                throw new \RuntimeException('Invalid status');
            }
        }

        $this->securityService->newLoginRequest($user->email, $_SERVER['REMOTE_ADDR'], false);

        // Throw InvalidCred exception if user doesn't exist or wrong password
        // Vague exception on purpose for security
        throw new InvalidCredentialsException($user->email);
    }
}