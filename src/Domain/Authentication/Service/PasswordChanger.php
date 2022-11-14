<?php

namespace App\Domain\Authentication\Service;

use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Authorization\UserAuthorizationChecker;
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
        private readonly PasswordVerifier $passwordVerifier,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('password-changer');
    }

    /**
     * Reset forgotten password with token received by mail
     *
     * @param string $password1
     * @param string $password2
     * @param int $tokenId
     * @param string $token
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
     * Normal password change with old password
     *
     * @param string $password1
     * @param string $password2
     * @param int $userId
     * @param string|null $oldPassword
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

        if (!$this->userAuthorizationChecker->isGrantedToUpdate(['password_without_verification' => 'value'],
                $userId) &&
            // Test password correctness only if user is allowed to change password hash as it's indicated to the user
            $this->userAuthorizationChecker->isGrantedToUpdate(['password_hash' => 'value'], $userId)
        ) {
            // Verify old password; throws validation exception if not correct old password
            $this->userValidator->validatePasswordCorrectness($oldPassword, 'old_password', $userId);
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
     * Change user password if authorized
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
            return $this->userUpdaterRepository->changeUserPassword($passwordHash, $userId);
        }

        // User does not have needed rights to access area or function
        $this->logger->warning('Failed attempt to change password of user id: ' . $userId);
        throw new ForbiddenException('Not allowed to change password.');
    }
}