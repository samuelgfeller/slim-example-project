<?php

namespace App\Module\Authentication\Register\Service;

use App\Module\Authentication\ChangeUserStatus\Service\UserStatusUpdater;
use App\Module\Authentication\Exception\UserAlreadyVerifiedException;
use App\Module\Authentication\TokenVerification\Exception\InvalidTokenException;
use App\Module\Authentication\TokenVerification\Repository\VerificationTokenFinderRepository;
use App\Module\Authentication\TokenVerification\Service\VerificationTokenUpdater;
use App\Module\User\Enum\UserActivity;
use App\Module\User\Enum\UserStatus;
use App\Module\User\Find\Service\UserFinder;
use App\Module\UserActivity\Create\Service\UserActivityLogger;

final readonly class RegisterTokenVerifier
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
    public function verifyRegisterTokenAndGetUserId(int $verificationId, string $token): int
    {
        $verification = $this->verificationTokenFinderRepository->findUserVerification($verificationId);
        if ($verification->userId) {
            $userStatus = $this->userFinder->findUserById($verification->userId)->status;

            // Check if user is already verified
            if (UserStatus::Unverified !== $userStatus) {
                // User is not unverified any more; that means that user already clicked on the link
                throw new UserAlreadyVerifiedException(
                    sprintf('User status is not "%s"', UserStatus::Unverified->value)
                );
            }
            // Check that verification has a token in the database and that the token is not used
            if ($verification->token !== null && $verification->usedAt === null) {
                // Verify given token with token in database
                if ($verification->expiresAt > time() && true === password_verify($token, $verification->token)) {
                    // Change user status to active
                    $hasUpdated = $this->userStatusUpdater->updateStatus(
                        UserStatus::Active,
                        $verification->userId
                    );
                    if ($hasUpdated === true) {
                        // Mark token as being used only after making sure that user is active
                        $this->verificationTokenUpdater->setVerificationEntryToUsed(
                            $verificationId,
                            $verification->userId
                        );
                        $userId = $this->verificationTokenFinderRepository->getUserIdFromVerification($verificationId);
                        // Add user activity entry
                        $this->userActivityLogger->logUserActivity(
                            UserActivity::UPDATED,
                            'user',
                            $userId,
                            ['status' => UserStatus::Active->value],
                            $userId,
                        );

                        return $userId;
                    }
                    // If somehow, the record could not be updated
                    throw new \DomainException(
                        'User status could not be set to "' . UserStatus::Active->value . '"'
                    );
                }
                // Same exception messages than AuthServiceUserVerificationTest.php
                throw new InvalidTokenException('Invalid or expired token.');
            }
        }
        throw new InvalidTokenException('No valid token was found.');
    }
}
