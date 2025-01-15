<?php

namespace App\Module\Authentication\UnlockAccount\Service;

use App\Module\Authentication\ChangeUserStatus\Service\UserStatusUpdater;
use App\Module\Authentication\Exception\UserAlreadyVerifiedException;
use App\Module\Authentication\TokenVerification\Exception\InvalidTokenException;
use App\Module\Authentication\TokenVerification\Repository\VerificationTokenFinderRepository;
use App\Module\Authentication\TokenVerification\Service\VerificationTokenUpdater;
use App\Module\User\Enum\UserActivity;
use App\Module\User\Enum\UserStatus;
use App\Module\User\Find\Service\UserFinder;
use App\Module\User\Service\UserFinderOld;
use App\Module\UserActivity\Create\Service\UserActivityLogger;

final readonly class AccountUnlockTokenVerifier
{
    public function __construct(
        private UserFinder $userFinder,
        private VerificationTokenFinderRepository $verificationTokenFinderRepository,
        private VerificationTokenUpdater $verificationTokenUpdater,
        private UserStatusUpdater $userStatusUpdater,
        private UserActivityLogger $userActivityLogger,
    ) {
    }

    /**
     * Verify token and return user id.
     *
     * @param int $verificationId
     * @param string $token
     *
     * @return int
     */
    public function verifyUnlockTokenAndGetUserId(int $verificationId, string $token): int
    {
        $verification = $this->verificationTokenFinderRepository->findUserVerification($verificationId);
        // Verify given token with token in database
        if (
            $verification->token !== null
            && $verification->usedAt === null
            && $verification->userId !== null
            && $verification->expiresAt > time()
            && true === password_verify($token, $verification->token)
        ) {
            $userStatus = $this->userFinder->findUserById($verification->userId)->status;

            // Check if user is locked at all
            if (UserStatus::Locked !== $userStatus) {
                // User is not locked any more, but token is not verified so VERY IMPORTANT to not authenticate
                // the user, hence the exception
                throw new UserAlreadyVerifiedException(__('User account not locked.'));
            }

            // Change user status to active
            $this->userStatusUpdater->updateStatus(UserStatus::Active, $verification->userId);
            // Mark token as being used only after setting the user status to active
            $this->verificationTokenUpdater->setVerificationEntryToUsed($verificationId, $verification->userId);
            $userId = $this->verificationTokenFinderRepository->getUserIdFromVerification($verificationId);
            // Add user activity entry
            $this->userActivityLogger->logUserActivity(
                UserActivity::UPDATED,
                'user',
                $userId,
                ['status' => UserStatus::Active->value],
                $userId
            );

            return $userId;
        }

        throw new InvalidTokenException('Not existing, invalid, used or expired token.');
    }
}
