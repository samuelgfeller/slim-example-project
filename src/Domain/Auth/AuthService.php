<?php


namespace App\Domain\Auth;

use App\Domain\Exceptions\InvalidCredentialsException;
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
        private RequestTrackRepository $requestTrackRepository
    ) {
    }

    /**
     * Checks if user is allowed to login.
     * If yes, the user object is returned with id
     * If no, an InvalidCredentialsException is thrown
     *
     * @param User $user
     * @return string id
     *
     * @throws InvalidCredentialsException
     *
     *
     */
    public function getUserIdIfAllowedToLogin(User $user): string
    {
        $this->userValidation->validateUserLogin($user);

        $dbUser = $this->userService->findUserByEmail($user->getEmail());
        if (isset($dbUser)) {
            if ($dbUser['status'] === User::STATUS_UNVERIFIED) {
                // todo inform user when he tries to login that account is unverified and he should click on the link in his inbox
                // maybe send verification email again
            } elseif ($dbUser['status'] === User::STATUS_SUSPENDED) {
                // Todo inform user (only via mail) that he is suspended and isn't allowed to create a new account
            } elseif ($dbUser['status'] === User::STATUS_LOCKED) {
                // Todo login fail and inform user (only via mail) that he is locked
            } elseif ($dbUser['status'] === User::STATUS_ACTIVE) {
                // Check failed login attempts
                if ($dbUser !== [] && password_verify($user->getPassword(), $dbUser['password_hash'])) {
                    $this->requestTrackRepository->newLoginRequest($dbUser['email'], $_SERVER['REMOTE_ADDR'], true);
                    return $dbUser['id'];
                }
            }
        }

        $this->requestTrackRepository->newLoginRequest($dbUser['email'], $_SERVER['REMOTE_ADDR'], false);

        // Throw InvalidCred exception if user doesn't exist or wrong password
        // (vague exception on purpose for security)
        throw new InvalidCredentialsException($user->getEmail());
    }

    /**
     * Insert user in database
     *
     * @param User $user
     * @return string|bool insert id, false if user already exists
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function registerUser(User $user): bool|string
    {
        // Validate user entries
        $this->userValidation->validateUserRegistration($user);

        // If user already exists
        if ($dbUser = $this->userRepository->findUserByEmail($user->getEmail())) {
            // If unverified and registered again, old user should be deleted and replaced with new input and verification
            // Reason: User could have lost the email or someone else tried to register under someone elses name
            if ($dbUser['status'] === User::STATUS_UNVERIFIED) {
                // Soft delete user so that new one can be inserted properly
                $this->userRepository->deleteUser($dbUser['id']);
                $this->userVerificationRepository->deleteWhere(['user_id' => $dbUser['id']]);
            } elseif ($dbUser['status'] === User::STATUS_SUSPENDED) {
                // Todo inform user (only via mail) that he is suspended and isn't allowed to create a new account
                return false;
            } else {
                try {
                    // Send info mail to email address holder
                    $this->emailService->setSubject('Someone tried to create an account with your address');
                    $this->emailService->setContentFromTemplate(
                        'auth/register-on-existing.email.php',
                        ['user' => $dbUser]
                    );
                    $this->emailService->setFrom('slim-example-project@samuel-gfeller.ch', 'Slim Example Project');
                    $this->emailService->sendTo($dbUser['email'], $dbUser['name']);
                    $this->requestTrackRepository->newEmailRequest($dbUser['email'], $_SERVER['REMOTE_ADDR']);
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
        $user->setId($this->userRepository->insertUser($user->toArrayForDatabase()));

        // Create, insert and send token to user
        $this->createAndSendUserVerification($user);

        $this->requestTrackRepository->newEmailRequest($user->getEmail(), $_SERVER['REMOTE_ADDR']);

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
        $this->userVerificationRepository->deleteWhere(['user_id' => $user->getId()]);

        // Insert verification token into database
        $tokenId = $this->userVerificationRepository->insert(
            [
                'user_id' => $user->getId(),
                'token' => password_hash($token, PASSWORD_DEFAULT),
                // expires format 'U' is the same as time() so it can be used later to compare easily
                'expires' => $expires->format('U')
            ]
        );

        // Send verification mail
        $this->emailService->setSubject('One more step to register');
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
     * @param string $verificationId
     * @param string $token
     * @return bool
     */
    public function verifyUser(string $verificationId, string $token): bool
    {
        $verification = $this->userVerificationRepository->findById($verificationId);
        if (([] !== $verification) && true === password_verify($token, $verification['token'])) {
            return $this->userRepository->setUserToVerified($verification['user_id']);
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
     * @param int $id
     * @return string
     */
    public function getUserRole(int $id): string
    {
        return $this->userRepository->getUserRole($id);
    }
}