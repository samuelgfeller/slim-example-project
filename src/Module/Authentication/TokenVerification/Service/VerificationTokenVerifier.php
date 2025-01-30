<?php

namespace App\Module\Authentication\TokenVerification\Service;

use App\Infrastructure\Database\Exception\PersistenceRecordNotFoundException;
use App\Module\Authentication\Register\Service\RegisterTokenVerifier;
use App\Module\Authentication\TokenVerification\Exception\InvalidTokenException;
use App\Module\Authentication\TokenVerification\Repository\VerificationTokenFinderRepository;
use App\Module\Authentication\UnlockAccount\Service\AccountUnlockTokenVerifier;

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
     * @throws PersistenceRecordNotFoundException
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
