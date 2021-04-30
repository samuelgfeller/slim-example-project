<?php


namespace App\Domain\Auth;

use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Security\SecurityService;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use App\Domain\Utility\EmailService;
use App\Infrastructure\Security\RequestTrackRepository;
use App\Infrastructure\User\UserRepository;
use App\Infrastructure\User\UserVerificationRepository;

/**
 * Authentication logic
 * Class AuthService
 * @package App\Domain\Auth
 */
class AuthService
{

    public function __construct(
        private UserValidation $userValidation,
        private UserService $userService,
        private EmailService $emailService,
        private UserRepository $userRepository,
        private UserVerificationRepository $userVerificationRepository,
        private SecurityService $securityService
    ) {
    }

    /**
     * Checks if user is allowed to login.
     * If yes, the user object is returned with id
     * If no, an InvalidCredentialsException is thrown
     *
     * @param User $user
     * @param string|null $captcha user captcha response if filled out
     * @return string id
     *
     * @throws InvalidCredentialsException
     */
    public function getUserIdIfAllowedToLogin(User $user, string|null $captcha = null): string
    {
        // Validate entries coming from client
        $this->userValidation->validateUserLogin($user);
        // Perform login security check
        $this->securityService->performLoginSecurityCheck($user->getEmail(), $captcha);

        $dbUser = $this->userService->findUserByEmail($user->getEmail());
        if (isset($dbUser) && $dbUser !== []) {
            if ($dbUser['status'] === User::STATUS_UNVERIFIED) {
                // todo inform user when he tries to login that account is unverified and he should click on the link in his inbox
                // maybe send verification email again and newEmailRequest (not login as its same as register)
            } elseif ($dbUser['status'] === User::STATUS_SUSPENDED) {
                // Todo inform user (only via mail) that he is suspended and isn't allowed to create a new account
            } elseif ($dbUser['status'] === User::STATUS_LOCKED) {
                // Todo login fail and inform user (only via mail) that he is locked
            } elseif ($dbUser['status'] === User::STATUS_ACTIVE) {
                // Check failed login attempts
                if (password_verify($user->getPassword(), $dbUser['password_hash'])) {
                    $this->securityService->newLoginRequest($dbUser['email'], $_SERVER['REMOTE_ADDR'], true);
                    return $dbUser['id'];
                }
            }
        }

        $this->securityService->newLoginRequest($user->getEmail(), $_SERVER['REMOTE_ADDR'], false);

        // Throw InvalidCred exception if user doesn't exist or wrong password
        // Vague exception on purpose for security
        throw new InvalidCredentialsException($user->getEmail());
    }

    /**
     * Insert user in database
     *
     * @param User $user
     * @param string|null $captcha user captcha response if filled out
     * @return string|bool insert id, false if user already exists
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function registerUser(User $user, string|null $captcha = null): bool|string
    {
        // Validate entries coming from client
        $this->userValidation->validateUserRegistration($user);

        $this->securityService->performEmailAbuseCheck($user->getEmail(), $captcha);

        $existingUser = $this->userRepository->findUserByEmail($user->getEmail());
        // Check if user already exists
        if ($existingUser->getEmail() !== null) {
            // If unverified and registered again, old user should be deleted and replaced with new input and verification
            // Reason: User could have lost the email or someone else tried to register under someone elses name
            if ($existingUser->getStatus() === User::STATUS_UNVERIFIED) {
                // Soft delete user so that new one can be inserted properly
                $this->userRepository->deleteUserById($existingUser->getId());
                $this->userVerificationRepository->deleteVerificationToken($existingUser->getId());
            } elseif ($existingUser->getStatus() === User::STATUS_SUSPENDED) {
                // Todo inform user (only via mail) that he is suspended and isn't allowed to create a new account
                return false;
            } else {
                try {
                    // Send info mail to email address holder
                    // Subject asserted in testRegisterUser_alreadyExistingActiveUser
                    $this->emailService->setSubject('Someone tried to create an account with your address');
                    $this->emailService->setContentFromTemplate(
                        'auth/register-on-existing.email.php',
                        ['user' => $existingUser]
                    );
                    $this->emailService->setFrom('slim-example-project@samuel-gfeller.ch', 'Slim Example Project');
                    $this->emailService->sendTo($existingUser->getEmail(), $existingUser->getName());
                    $this->securityService->newEmailRequest($existingUser->getEmail(), $_SERVER['REMOTE_ADDR']);
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    // We try to hide if an email already exists or not so if email fails, nothing is done
                } catch (\Throwable $e) { // For phpRenderer ->fetch()
                }
                return false;
            }
        }

        $user->setPasswordHash(password_hash($user->getPassword(), PASSWORD_DEFAULT));

        $user->setStatus(User::STATUS_UNVERIFIED);

        // Insert new user into database
        $user->setId($this->userRepository->insertUser($user));

        // Create, insert and send token to user
        $this->createAndSendUserVerification($user);

        $this->securityService->newEmailRequest($user->getEmail(), $_SERVER['REMOTE_ADDR']);

        return $user->getId();
    }

    /**
     * Create and insert verification token
     *
     * @param User $user WITH id
     * @return string
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function createAndSendUserVerification(User $user): string
    {
        // Create tokens
        $token = random_bytes(50);

        // Token expiration
        $expires = new \DateTime('now');
        $expires->add(new \DateInterval('PT01H')); // 1 hour

        // Soft delete any existing tokens for this user
        $this->userVerificationRepository->deleteVerificationToken($user->getId());

        // Insert verification token into database
        $tokenId = $this->userVerificationRepository->insertUserVerification(
            [
                'user_id' => $user->getId(),
                'token' => password_hash($token, PASSWORD_DEFAULT),
                // expires format 'U' is the same as time() so it can be used later to compare easily
                'expires' => $expires->format('U')
            ]
        );

        // Send verification mail
        $this->emailService->setSubject('One more step to register'); // Subject asserted in testRegisterUser
        $this->emailService->setContentFromTemplate(
            'auth/register.email.php',
            ['user' => $user, 'token' => $token, 'id' => $tokenId]
        );
        $this->emailService->setFrom('slim-example-project@samuel-gfeller.ch', 'Slim Example Project');
        $this->emailService->sendTo($user->getEmail(), $user->getName());
        // PHPMailer errors caught in action

        return $tokenId;
    }

    /**
     * Verify token
     * @param int $verificationId
     * @param string $token
     * @return bool
     */
    public function verifyUser(int $verificationId, string $token): bool
    {
        $verification = $this->userVerificationRepository->findUserVerification($verificationId);
        if (([] !== $verification) && true === password_verify($token, $verification['token'])) {
            return $this->userRepository->changeUserStatus(User::STATUS_ACTIVE, $verification['user_id']);
        }
        return false;
    }

    /**
     * @param string $verificationId
     */
    public function getUserIdFromVerification(string $verificationId): string
    {
        return $this->userVerificationRepository->getUserIdFromVerification($verificationId);
    }

    /**
     * Get user role
     *
     * @param int $userId
     * @return string
     */
    public function getUserRoleById(int $userId): string
    {
        return $this->userRepository->getUserRole($userId);
    }
}