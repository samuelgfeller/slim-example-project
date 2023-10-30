<?php

namespace App\Domain\Authentication\Service;

use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Domain\Authentication\Exception\UserAlreadyVerifiedException;
use App\Domain\Authentication\Repository\VerificationToken\VerificationTokenFinderRepository;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Repository\UserUpdaterRepository;
use App\Domain\User\Service\UserFinder;
use App\Domain\UserActivity\Service\UserActivityLogger;

final class RegisterTokenVerifier
{
    public function __construct(
        private readonly UserFinder $userFinder,
        private readonly VerificationTokenFinderRepository $verificationTokenFinderRepository,
        private readonly VerificationTokenUpdater $verificationTokenUpdater,
        private readonly UserUpdaterRepository $userUpdaterRepository,
        private readonly UserActivityLogger $userActivityLogger,
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
    public function getUserIdIfRegisterTokenIsValid(int $verificationId, string $token): int
    {
        $verification = $this->verificationTokenFinderRepository->findUserVerification($verificationId);
        if ($verification->userId) {
            $userStatus = $this->userFinder->findUserById($verification->userId)->status;

            // Check if user is already verified
            if (UserStatus::Unverified !== $userStatus) {
                // User is not unverified anymore, that means that user already clicked on the link
                throw new UserAlreadyVerifiedException(
                    sprintf('User status is not "%s"', UserStatus::Unverified->value)
                );
            }
            // Check that verification has token in the database and token is not used
            if ($verification->token !== null && $verification->usedAt === null) {
                // Verify given token with token in database
                if ($verification->expiresAt > time() && true === password_verify($token, $verification->token)) {
                    // Change user status to active
                    $hasUpdated = $this->userUpdaterRepository->changeUserStatus(
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
                    // If somehow the record could not be updated
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
