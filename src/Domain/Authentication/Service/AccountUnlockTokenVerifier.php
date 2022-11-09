<?php


namespace App\Domain\Authentication\Service;


use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Domain\Authentication\Exception\UserAlreadyVerifiedException;
use App\Domain\User\Enum\UserStatus;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenFinderRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenUpdaterRepository;
use App\Infrastructure\User\UserFinderRepository;
use App\Infrastructure\User\UserUpdaterRepository;

final class AccountUnlockTokenVerifier
{
    public function __construct(
        private UserFinderRepository $userFinderRepository,
        private VerificationTokenFinderRepository $verificationTokenFinderRepository,
        private VerificationTokenUpdaterRepository $verificationTokenUpdaterRepository,
        private UserUpdaterRepository $userUpdaterRepository
    ) {
    }

    /**
     * Verify token and return user id
     *
     * @param int $verificationId
     * @param string $token
     * @return int
     */
    public function getUserIdIfUnlockTokenIsValid(int $verificationId, string $token): int
    {

        $verification = $this->verificationTokenFinderRepository->findUserVerification($verificationId);
        $userStatus = $this->userFinderRepository->findUserById($verification->userId)->status;

        // Check if user is locked at all
        if (UserStatus::STATUS_LOCKED !== $userStatus) {
            // User is not locked anymore but token not verified so VERY IMPORTANT to not log user in hence the exception
            throw new UserAlreadyVerifiedException('User account not locked anymore. Please log in.');
        }

        // Verify given token with token in database
        if (
            $verification->token !== null &&
            $verification->usedAt === null &&
            $verification->expiresAt > time() &&
            true === password_verify($token, $verification->token)
        ) {

            // Change user status to active
            $this->userUpdaterRepository->changeUserStatus(UserStatus::STATUS_ACTIVE, $verification->userId);
            // Mark token as being used only after making sure that user is active
            $this->verificationTokenUpdaterRepository->setVerificationEntryToUsed($verificationId);
            return $this->verificationTokenFinderRepository->getUserIdFromVerification($verificationId);
        }

        throw new InvalidTokenException('Not existing, invalid, used or expired token.');
    }
}