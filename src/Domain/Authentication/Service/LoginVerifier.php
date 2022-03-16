<?php


namespace App\Domain\Authentication\Service;


use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Security\Service\SecurityLoginChecker;
use App\Domain\User\Data\UserData;
use App\Domain\User\Service\UserValidator;
use App\Infrastructure\Security\RequestCreatorRepository;
use App\Infrastructure\User\UserFinderRepository;

class LoginVerifier
{

    public function __construct(
        private UserValidator $userValidator,
        private SecurityLoginChecker $loginSecurityChecker,
        private UserFinderRepository $userFinderRepository,
        private RequestCreatorRepository $requestCreatorRepo,
        private LoginNonActiveUserHandler $loginNonActiveUserHandler,
    ) { }

    /**
     * Checks if user is allowed to login.
     * If yes, the user object is returned with id
     * If no, an InvalidCredentialsException is thrown
     *
     * @param array $userData
     * @param string|null $captcha user captcha response if filled out
     * @return int id
     *
     */
    public function getUserIdIfAllowedToLogin(array $userData, string|null $captcha = null, array $queryParams = []): int
    {
        $user = new UserData($userData, true);

        // Validate entries coming from client
        $this->userValidator->validateUserLogin($user);

        // Perform login security check
        $this->loginSecurityChecker->performLoginSecurityCheck($user->email, $captcha);

        $dbUser = $this->userFinderRepository->findUserByEmail($user->email);
        // Check if user already exists
        if ($dbUser->email !== null) {
            if ($dbUser->status === UserData::STATUS_UNVERIFIED) {
                // todo inform user when he tries to login that account is unverified and he should click on the link in his inbox
                // maybe send verification email again and newEmailRequest (not login as its same as register)
                $this->loginNonActiveUserHandler->handleUnverifiedUser($user, $queryParams);
                // Todo display error message to user in form and test email
            } elseif ($dbUser->status === UserData::STATUS_SUSPENDED) {
                // Todo inform user (only via mail) that he is suspended and isn't allowed to create a new account
            } elseif ($dbUser->status === UserData::STATUS_LOCKED) {
                // Todo login fail and inform user (only via mail) that he is locked
            } elseif ($dbUser->status === UserData::STATUS_ACTIVE) {
                // Check failed login attempts
                if (password_verify($user->password, $dbUser->passwordHash)) {
                    $this->requestCreatorRepo->insertLoginRequest($dbUser->email, $_SERVER['REMOTE_ADDR'], true);
                    return $dbUser->id;
                }
            } else {
                // todo invalid role in db. Send email to admin to inform that there is something wrong with the user
                throw new \RuntimeException('Invalid status');
            }
        }

        $this->requestCreatorRepo->insertLoginRequest($user->email, $_SERVER['REMOTE_ADDR'], false);

        // Throw InvalidCred exception if user doesn't exist or wrong password
        // Vague exception on purpose for security
        throw new InvalidCredentialsException($user->email);
    }
}