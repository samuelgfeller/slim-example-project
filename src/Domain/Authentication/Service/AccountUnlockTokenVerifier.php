<?php


namespace App\Domain\Authentication\Service;


use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Domain\Authentication\Exception\UserAlreadyVerifiedException;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenFinderRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenUpdaterRepository;
use App\Infrastructure\User\UserUpdaterRepository;

final class AccountUnlockTokenVerifier
{
    public function __construct(
        private readonly UserFinder $userFinder,
        private readonly VerificationTokenFinderRepository $verificationTokenFinderRepository,
        private readonly VerificationTokenUpdaterRepository $verificationTokenUpdaterRepository,
        private readonly UserUpdaterRepository $userUpdaterRepository
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
        $userStatus = $this->userFinder->findUserById($verification->userId)->status;

        // Check if user is locked at all
        if (UserStatus::Locked !== $userStatus) {
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
            $this->userUpdaterRepository->changeUserStatus(UserStatus::Active, $verification->userId);
            // Mark token as being used only after making sure that user is active
            $this->verificationTokenUpdaterRepository->setVerificationEntryToUsed($verificationId);
            return $this->verificationTokenFinderRepository->getUserIdFromVerification($verificationId);
        }

        throw new InvalidTokenException('Not existing, invalid, used or expired token.');
    }
}