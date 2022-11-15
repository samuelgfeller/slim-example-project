<?php


namespace App\Domain\Authentication\Service;


use App\Domain\Authentication\Exception\UnableToLoginStatusNotActiveException;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Security\Service\SecurityLoginChecker;
use App\Domain\Settings;
use App\Domain\User\Data\UserData;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserValidator;
use App\Infrastructure\Security\RequestCreatorRepository;
use App\Infrastructure\User\UserFinderRepository;
use Symfony\Component\Mailer\Exception\TransportException;

class LoginVerifier
{

    private string $mainContactEmail;

    public function __construct(
        private readonly UserValidator $userValidator,
        private readonly SecurityLoginChecker $loginSecurityChecker,
        private readonly UserFinderRepository $userFinderRepository,
        private readonly RequestCreatorRepository $requestCreatorRepo,
        private readonly LoginNonActiveUserHandler $loginNonActiveUserHandler,
        readonly Settings $settings
    ) {
        $this->mainContactEmail = $this->settings->get(
            'public'
        )['email']['main_contact_address'] ?? 'slim-example-project@samuel-gfeller.ch';
    }

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
    public function getUserIdIfAllowedToLogin(
        array $userData,
        string|null $captcha = null,
        array $queryParams = []
    ): int {
        $user = new UserData($userData, true);

        // Validate entries coming from client
        $this->userValidator->validateUserLogin($user);

        // Perform login security check
        $this->loginSecurityChecker->performLoginSecurityCheck($user->email, $captcha);

        $dbUser = $this->userFinderRepository->findUserByEmail($user->email);
        // Check if user exists
        if ($dbUser->email !== null) {
            // Verify if password matches and enter login request
            if (password_verify($user->password, $dbUser->passwordHash)) {
                // If password correct and status active, log user in by
                if ($dbUser->status === UserStatus::Active) {
                    // Insert login success request
                    $this->requestCreatorRepo->insertLoginRequest($dbUser->email, $_SERVER['REMOTE_ADDR'], true);
                    // Return id (not sure if it's better to regenerate session here in service or in action)
                    return $dbUser->id;
                }

                // If status not active, create exception object
                $unableToLoginException = new UnableToLoginStatusNotActiveException(
                    'Unable to login at the moment, please check your email inbox for a more detailed message.'
                );
                try {
                    if ($dbUser->status === UserStatus::Unverified) {
                        // Inform user via email that account is unverified, and he should click on the link in his inbox
                        $this->loginNonActiveUserHandler->handleUnverifiedUserLoginAttempt($dbUser, $queryParams);
                        // Throw exception to display error message in form
                        throw $unableToLoginException;
                    }

                    if ($dbUser->status === UserStatus::Suspended) {
                        // Inform user (only via mail) that he is suspended
                        $this->loginNonActiveUserHandler->handleSuspendedUserLoginAttempt($dbUser);
                        // Throw exception to display error message in form
                        throw $unableToLoginException;
                    }

                    if ($dbUser->status === UserStatus::Locked) {
                        // login fail and inform user (only via mail) that he is locked and provide unlock token
                        $this->loginNonActiveUserHandler->handleLockedUserLoginAttempt($dbUser, $queryParams);
                        // Throw exception to display error message in form
                        throw $unableToLoginException;
                    }
                } catch (TransportException $transportException) {
                    // Exception while sending email
                    throw new UnableToLoginStatusNotActiveException(
                        'Unable to login at the moment and there was an error when sending an email to you.' .
                        "\n Please contact $this->mainContactEmail."
                    );
                }

                // todo invalid status in db. Send email to admin to inform that there is something wrong with the user
                throw new \RuntimeException('Invalid status');
            }
        }
        // Password not correct or user not existing - insert login request for ip
        $this->requestCreatorRepo->insertLoginRequest($user->email, $_SERVER['REMOTE_ADDR'], false);

        // Throw InvalidCred exception if user doesn't exist or wrong password
        // Vague exception on purpose for security
        throw new InvalidCredentialsException($user->email);
    }
}