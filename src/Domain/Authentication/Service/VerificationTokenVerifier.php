<?php

namespace App\Domain\Authentication\Service;

use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Domain\Authentication\Repository\VerificationToken\VerificationTokenFinderRepository;

final readonly class VerificationTokenVerifier
{
    public function __construct(
        private VerificationTokenFinderRepository $verificationTokenFinderRepository,
        private VerificationTokenUpdater $verificationTokenUpdater,
    ) {
    }

    /**
     * Most simple form of verifying token and return user id.
     *
     * @param string $token
     * @param int $verificationId
     *
     * @throws InvalidTokenException
     *
     * @return int
     *
     * @see AccountUnlockTokenVerifier, RegisterTokenVerifier
     */
    public function verifyTokenAndGetUserId(int $verificationId, string $token): int
    {
        $verification = $this->verificationTokenFinderRepository->findUserVerification($verificationId);

        // Verify given token with token in database
        if (
            ($verification->token !== null) && $verification->usedAt === null && $verification->expiresAt > time()
            && true === password_verify($token, $verification->token)
        ) {
            // Mark token as being used if it was correct and not expired
            $this->verificationTokenUpdater->setVerificationEntryToUsed($verificationId, $verification->userId);

            return $this->verificationTokenFinderRepository->getUserIdFromVerification($verificationId);
        }

        $invalidTokenException = new InvalidTokenException('Not existing, invalid, used or expired token.');
        // Add user details to invalid token exception
        $invalidTokenException->userData = $this->verificationTokenFinderRepository
            ->findUserDetailsByVerificationIncludingDeleted($verificationId);

        throw $invalidTokenException;
    }
}
