<?php

namespace App\Domain\Authentication\Service;

use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Repository\UserUpdaterRepository;
use App\Domain\UserActivity\Service\UserActivityLogger;
use Psr\Log\LoggerInterface;

/**
 * Validate password when resetting it with token (after forgotten password).
 */
final readonly class PasswordResetterWithToken
{
    public function __construct(
        private UserUpdaterRepository $userUpdaterRepository,
        private AuthenticationValidator $authenticationValidator,
        private VerificationTokenVerifier $verificationTokenVerifier,
        private UserActivityLogger $userActivityLogger,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Reset forgotten password with token received by mail.
     *
     * @param array $passwordResetValues
     *
     * @return bool
     */
    public function resetPasswordWithToken(array $passwordResetValues): bool
    {
        // Validate passwords BEFORE token verification as it would be set to usedAt even if passwords are not valid
        $this->authenticationValidator->validatePasswordReset($passwordResetValues);
        // If passwords are valid strings, verify token and set token to used
        $userId = $this->verificationTokenVerifier->verifyTokenAndGetUserId(
            $passwordResetValues['id'],
            $passwordResetValues['token']
        );

        // Intentionally NOT logging user in so that they have to confirm the correctness of their credentials
        $passwordHash = password_hash($passwordResetValues['password'], PASSWORD_DEFAULT);
        $updated = $this->userUpdaterRepository->changeUserPassword($passwordHash, $userId);
        if ($updated) {
            $this->userActivityLogger->logUserActivity(
                UserActivity::UPDATED,
                'user',
                $userId,
                ['password_hash' => '******']
            );
            $this->logger->info(sprintf('Password was reset for user %s', $userId));

            return true;
        }
        // If somehow, the record could not be updated
        throw new \DomainException('Password could not be reset. Please try again or contact support.');
    }
}
