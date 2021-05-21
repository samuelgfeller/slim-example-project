<?php


namespace App\Domain\Authentication\Service;


use App\Domain\Security\Service\SecurityEmailChecker;
use App\Domain\User\DTO\User;
use App\Domain\User\Service\UserValidator;
use App\Domain\Utility\EmailService;
use App\Infrastructure\Authentication\UserRegistererRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenCreatorRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenDeleterRepository;
use App\Infrastructure\Security\RequestCreatorRepository;
use App\Infrastructure\User\UserDeleterRepository;
use App\Infrastructure\User\UserFinderRepository;

class UserRegisterer
{
    public function __construct(
        private UserValidator $userValidator,
        private SecurityEmailChecker $emailSecurityChecker,
        private UserDeleterRepository $userDeleterRepository,
        private UserFinderRepository $userFinderRepository,
        private UserRegistererRepository $userRegistererRepository,
        private VerificationTokenDeleterRepository $verificationTokenDeleterRepository,
        private VerificationTokenCreatorRepository $verificationTokenCreatorRepository,
        private EmailService $emailService,
        private RequestCreatorRepository $requestCreatorRepo
    ) { }

    /**
     * Insert user in database
     *
     * @param array $userData
     * @param string|null $captcha user captcha response if filled out
     * @param array $queryParams query params that should be added to email verification link (e.g. redirect)
     *
     * @return int|bool insert id, false if user already exists
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function registerUser(array $userData, string|null $captcha = null, array $queryParams = []): bool|int
    {
        $user = new User($userData, true);

        // Validate entries coming from client
        $this->userValidator->validateUserRegistration($user);

        $this->emailSecurityChecker->performEmailAbuseCheck($user->email, $captcha);

        $existingUser = $this->userFinderRepository->findUserByEmail($user->email);
        // Check if user already exists
        if ($existingUser->email !== null) {
            // If unverified and registered again, old user should be deleted and replaced with new input and verification
            // Reason: User could have lost the email or someone else tried to register under someone elses name
            if ($existingUser->status === User::STATUS_UNVERIFIED) {
                // Soft delete user so that new one can be inserted properly
                $this->userDeleterRepository->deleteUserById($existingUser->id);
                $this->verificationTokenDeleterRepository->deleteVerificationToken($existingUser->id);
            } elseif ($existingUser->status === User::STATUS_SUSPENDED) {
                // Todo inform user (only via mail) that he is suspended and isn't allowed to create a new account
                return false;
            } elseif ($existingUser->status === User::STATUS_LOCKED) {
                // Todo inform user (only via mail) that he is locked and can't create a new account
                return false;
            } elseif ($existingUser->status === User::STATUS_ACTIVE) {
                try {
                    // Send info mail to email address holder
                    // Subject asserted in testRegisterUser_alreadyExistingActiveUser
                    $this->emailService->setSubject('Someone tried to create an account with your address');
                    $this->emailService->setContentFromTemplate(
                        'Authentication/register-on-existing.email.php',
                        ['user' => $existingUser]
                    );
                    $this->emailService->setFrom('slim-example-project@samuel-gfeller.ch', 'Slim Example Project');
                    $this->emailService->sendTo($existingUser->email, $existingUser->name);
                    $this->requestCreatorRepo->insertEmailRequest($existingUser->email, $_SERVER['REMOTE_ADDR']);
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    // We try to hide if an email already exists or not so if email fails, nothing is done
                } catch (\Throwable $e) { // For phpRenderer ->fetch()
                }
                return false;
            } else {
                // todo invalid role in db. Send email to admin to inform that there is something wrong with the user
                throw new \RuntimeException('Invalid role');
            }
        }

        $user->passwordHash = password_hash($user->password, PASSWORD_DEFAULT);

        // Set default status and role
        $user->status = User::STATUS_UNVERIFIED;
        $user->role = 'user';

        // Insert new user into database
        $user->id = $this->userRegistererRepository->insertUser($user);

        // Create, insert and send token to user
        $this->createAndSendUserVerification($user, $queryParams);

        $this->requestCreatorRepo->insertEmailRequest($user->email, $_SERVER['REMOTE_ADDR']);

        return $user->id;
    }

    /**
     * Create and insert verification token
     *
     * @param User $user WITH id
     * @param array $queryParams query params that should be added to email verification link (e.g. redirect)
     *
     * @return int
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function createAndSendUserVerification(User $user, array $queryParams = []): int
    {
        // Create token
        $token = random_bytes(50);

        // Set token expiration because link automatically logs in
        $expires = new \DateTime('now');
        $expires->add(new \DateInterval('PT02H')); // 2 hours

        // Soft delete any existing tokens for this user
        $this->verificationTokenDeleterRepository->deleteVerificationToken($user->id);

        // Insert verification token into database
        $tokenId = $this->verificationTokenCreatorRepository->insertUserVerification(
            [
                'user_id' => $user->id,
                'token' => password_hash($token, PASSWORD_DEFAULT),
                // expires format 'U' is the same as time() so it can be used later to compare easily
                'expires' => $expires->format('U')
            ]
        );

        // Add relevant query params to $queryParams array
        $queryParams['token'] = $token;
        $queryParams['id'] = $tokenId;

        // Send verification mail
        $this->emailService->setSubject('One more step to register'); // Subject asserted in testRegisterUser
        $this->emailService->setContentFromTemplate(
            'auth/register.email.php',
            ['user' => $user, 'queryParams' => $queryParams]
        );
        $this->emailService->setFrom('slim-example-project@samuel-gfeller.ch', 'Slim Example Project');
        $this->emailService->sendTo($user->email, $user->name);
        // PHPMailer errors caught in action

        return $tokenId;
    }
}