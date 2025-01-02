<?php

namespace App\Modules\Authentication\Domain\Service;

use App\Modules\Authentication\Repository\VerificationToken\VerificationTokenCreatorRepository;
use App\Modules\Authentication\Repository\VerificationToken\VerificationTokenDeleterRepository;
use App\Modules\User\Enum\UserActivity;
use App\Modules\UserActivity\Service\UserActivityLogger;

final readonly class VerificationTokenCreator
{
    public function __construct(
        private VerificationTokenDeleterRepository $verificationTokenDeleterRepository,
        private VerificationTokenCreatorRepository $verificationTokenCreatorRepository,
        private UserActivityLogger $userActivityLogger,
    ) {
    }

    /**
     * Create and insert verification token.
     *
     * @param int $userId
     * @param array $queryParams query params that should be added to email verification link (e.g. redirect)
     *
     * @return array $queryParams with token and id
     */
    public function createUserVerification(int $userId, array $queryParams = []): array
    {
        // Create token
        $token = bin2hex(random_bytes(50));

        // Set token expiration datetime
        $expiresAt = new \DateTime('now');
        $expiresAt->add(new \DateInterval('PT02H')); // 2 hours

        // Delete any existing tokens for this user
        $this->verificationTokenDeleterRepository->deleteVerificationToken($userId);

        // Insert verification token into database
        $userVerificationRow = [
            'user_id' => $userId,
            'token' => password_hash($token, PASSWORD_DEFAULT),
            // expiresAt format 'U' is the same as time() so it can be used later to compare easily
            'expires_at' => $expiresAt->format('U'),
        ];
        $tokenId = $this->verificationTokenCreatorRepository->insertUserVerification($userVerificationRow);

        // Add relevant query params to $queryParams array
        $queryParams['token'] = $token;
        $queryParams['id'] = $tokenId;

        // Add user activity entry
        $userVerificationRow['token'] = '******';
        $this->userActivityLogger->logUserActivity(
            UserActivity::CREATED,
            'user_verification',
            $tokenId,
            $userVerificationRow,
            $userId,
        );

        return $queryParams;
    }
}
