<?php

namespace App\Domain\Authentication\Service;

use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Repository\UserUpdaterRepository;
use App\Domain\User\Service\UserValidator;
use App\Domain\UserActivity\Service\UserActivityLogger;
use Psr\Log\LoggerInterface;

class PasswordResetterWithToken
{
    public function __construct(
        private readonly UserUpdaterRepository $userUpdaterRepository,
        private readonly UserValidator $userValidator,
        private readonly VerificationTokenVerifier $verificationTokenVerifier,
        private readonly UserActivityLogger $userActivityLogger,
        private readonly LoggerInterface $logger,
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
        $this->userValidator->validatePasswordReset($passwordResetValues);
        // If passwords are valid strings, verify token and set token to used
        $userId = $this->verificationTokenVerifier->getUserIdIfTokenIsValid(
            $passwordResetValues['id'],
            $passwordResetValues['token']
        );

        // Intentionally NOT logging user in so that he has to confirm the correctness of his credential
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
        $this->logger->info(sprintf('Password reset failed for user %s', $userId));

        return false;
    }
}
