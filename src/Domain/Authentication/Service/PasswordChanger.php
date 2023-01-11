<?php

namespace App\Domain\Authentication\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Authorization\UserAuthorizationChecker;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Service\UserActivityManager;
use App\Domain\User\Service\UserValidator;
use App\Infrastructure\User\UserUpdaterRepository;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class PasswordChanger
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
        private readonly SessionInterface $session,
        private readonly UserUpdaterRepository $userUpdaterRepository,
        private readonly UserValidator $userValidator,
        private readonly VerificationTokenVerifier $verificationTokenVerifier,
        private readonly UserActivityManager $userActivityManager,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('password-changer');
    }

    /**
     * Reset forgotten password with token received by mail.
     *
     * @param string $password1
     * @param string $password2
     * @param int $tokenId
     * @param string $token
     *
     * @return bool
     */
    public function resetPasswordWithToken(string $password1, string $password2, int $tokenId, string $token): bool
    {
        // Validate passwords BEFORE token as token would be set to usedAt even if passwords are not valid
        $this->userValidator->validatePasswords([$password1, $password2], true);
        // If passwords are valid strings, verify token and set token as used
        $userId = $this->verificationTokenVerifier->getUserIdIfTokenIsValid($tokenId, $token);

        // Log user in - session needed to be authorized to change password
        // Clear all session data and regenerate session ID
        $this->session->regenerateId();
        // Add user to session
        $this->session->set('user_id', $userId);

        // Call function to change password AFTER login as session is needed to pass userAuthorizationChecker
        return $this->updateUserPassword($password1, $userId);
    }

    /**
     * Normal password change with old password.
     *
     * @param string $password1
     * @param string $password2
     * @param int $userId
     * @param string|null $oldPassword
     *
     * @return bool
     */
    public function changeUserPassword(
        string $password1,
        string $password2,
        int $userId,
        ?string $oldPassword = null
    ): bool {
        // Check if password strings are valid
        $this->userValidator->validatePasswords([$password1, $password2], true);

        // If old password is provided and user is allowed to update password, test password correctness
        if ($oldPassword &&
            $this->userAuthorizationChecker->isGrantedToUpdate(['password_hash' => 'value'], $userId)
        ) {
            // Verify old password; throws validation exception if not correct old password
            $this->userValidator->checkIfPasswordIsCorrect($oldPassword, 'old_password', $userId);
        } // Else if old password is set, check if granted to update without verification. If not, user not allowed to
        // change password
        elseif (!$oldPassword && (!$this->userAuthorizationChecker->isGrantedToUpdate(
            ['password_without_verification' => 'value'],
            $userId,
        ) || !$this->userAuthorizationChecker->isGrantedToUpdate(['password_hash' => 'value'], $userId))
        ) {
            throw new ForbiddenException('Not granted to update password without verification.');
        }

        // Calls service function to check if authorized and change password
        $passwordUpdated = $this->updateUserPassword($password1, $userId);

        // Clear all session data and regenerate session ID if changed user is logged in
        if ((int)$this->session->get('user_id') === $userId) {
            $this->session->regenerateId();
        }

        return $passwordUpdated;
    }

    /**
     * Change user password if authorized.
     *
     * @param string $newPassword
     * @param int $userId
     *
     * @return bool
     */
    private function updateUserPassword(string $newPassword, int $userId): bool
    {
        if ($this->userAuthorizationChecker->isGrantedToUpdate(['password_hash' => 'value'], $userId)) {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updated = $this->userUpdaterRepository->changeUserPassword($passwordHash, $userId);
            if ($updated) {
                $this->userActivityManager->addUserActivity(
                    UserActivity::UPDATED,
                    'user',
                    $userId,
                    ['password_hash' => '******']
                );
            }

            return $updated;
        }

        // User does not have needed rights to access area or function
        $this->logger->warning('Failed attempt to change password of user id: ' . $userId);
        throw new ForbiddenException('Not allowed to change password.');
    }
}
