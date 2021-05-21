<?php


namespace App\Domain\Authentication\Service;


use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Domain\Authentication\Exception\UserAlreadyVerifiedException;
use App\Domain\User\DTO\User;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenFinderRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenUpdaterRepository;
use App\Infrastructure\User\UserFinderRepository;
use App\Infrastructure\User\UserUpdaterRepository;

final class RegisterTokenVerifier
{
    public function __construct(
        private UserFinderRepository $userFinderRepository,
        private VerificationTokenFinderRepository $verificationTokenFinderRepository,
        private VerificationTokenUpdaterRepository $verificationTokenUpdaterRepository,
        private UserUpdaterRepository $userUpdaterRepository
    ) { }

    /**
     * Verify token and return user id
     *
     * @param int $verificationId
     * @param string $token
     * @return int
     */
    public function getUserIdIfTokenIsValid(int $verificationId, string $token): int
    {
        $verification = $this->verificationTokenFinderRepository->findUserVerification($verificationId);

        if ($verification->token !== null) {
            $userStatus = $this->userFinderRepository->findUserById($verification->userId)->status;
            // Check if user is already verified
            if (User::STATUS_UNVERIFIED !== $userStatus) {
                // User is not unverified anymore, that means that user already clicked on the link
                throw new UserAlreadyVerifiedException('User has not status "' . User::STATUS_UNVERIFIED . '"');
            } // Verify given token with token in database
            elseif ($verification->expires > time() && true === password_verify($token, $verification->token)) {
                // Change user status to active
                $hasUpdated = $this->userUpdaterRepository->changeUserStatus(User::STATUS_ACTIVE, $verification->userId);
                if ($hasUpdated === true) {
                    // Mark token as being used only after making sure that user is active
                    $this->verificationTokenUpdaterRepository->setVerificationEntryToUsed($verificationId);
                    return $this->verificationTokenFinderRepository->getUserIdFromVerification($verificationId);
                }
                // If somehow the record could not be updated
                throw new \DomainException('User status could not be set to "' . User::STATUS_ACTIVE . '"');
            }
            // Same exception messages than AuthServiceUserVerificationTest.php
            throw new InvalidTokenException('Invalid or expired token.');
        }
        // If no token was found and user is still unverified, that means that the token is invalid
        throw new InvalidTokenException('No token was found for id "' . $verificationId . '".');
    }
}