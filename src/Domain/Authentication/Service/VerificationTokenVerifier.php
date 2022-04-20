<?php


namespace App\Domain\Authentication\Service;


use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenFinderRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenUpdaterRepository;

final class VerificationTokenVerifier
{
    public function __construct(
        private VerificationTokenFinderRepository $verificationTokenFinderRepository,
        private VerificationTokenUpdaterRepository $verificationTokenUpdaterRepository,
    ) {
    }

    /**
     * Most simple form of verifying token and return user id
     *
     * Token also verified @param int $verificationId
     * @param string $token
     * @return int
     *
     * @throws InvalidTokenException
     * @see AccountUnlockTokenVerifier, RegisterTokenVerifier
     *
     */
    public function getUserIdIfTokenIsValid(int $verificationId, string $token): int
    {
        $verification = $this->verificationTokenFinderRepository->findUserVerification($verificationId);

        // Verify given token with token in database
        if (
            ($verification->token !== null) && $verification->usedAt === null && $verification->expiresAt > time() &&
            true === password_verify($token, $verification->token)
        ) {
            // Mark token as being used
            $this->verificationTokenUpdaterRepository->setVerificationEntryToUsed($verificationId);
            return $this->verificationTokenFinderRepository->getUserIdFromVerification($verificationId);
        }

        $invalidTokenException = new InvalidTokenException('Not existing, invalid, used or expired token.');
        // Add user details to invalid token exception
        $invalidTokenException->userData = $this->verificationTokenFinderRepository->findUserDetailsFromAlsoDeletedVerification(
            $verificationId
        );

        throw $invalidTokenException;
    }
}