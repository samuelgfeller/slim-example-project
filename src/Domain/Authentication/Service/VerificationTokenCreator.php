<?php

namespace App\Domain\Authentication\Service;

use App\Domain\User\Data\UserData;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Service\UserActivityManager;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenCreatorRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenDeleterRepository;

class VerificationTokenCreator
{
    public function __construct(
        private readonly VerificationTokenDeleterRepository $verificationTokenDeleterRepository,
        private readonly VerificationTokenCreatorRepository $verificationTokenCreatorRepository,
        private readonly UserActivityManager $userActivityManager,
    ) {
    }

    /**
     * Create and insert verification token.
     *
     * @param UserData $user WITH id
     * @param array $queryParams query params that should be added to email verification link (e.g. redirect)
     *
     * @return array $queryParams with token and id
     */
    public function createUserVerification(UserData $user, array $queryParams = []): array
    {
        // Create token
        $token = bin2hex(random_bytes(50));

        // Set token expiration because link automatically logs in
        $expiresAt = new \DateTime('now');
        $expiresAt->add(new \DateInterval('PT02H')); // 2 hours

        // Soft delete any existing tokens for this user
        $this->verificationTokenDeleterRepository->deleteVerificationToken($user->id);

        // Insert verification token into database
        $userVerificationRow = [
            'user_id' => $user->id,
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
        $this->userActivityManager->addUserActivity(
            UserActivity::CREATED,
            'user_verification',
            $tokenId,
            $userVerificationRow,
            $user->id,
        );

        return $queryParams;
    }
}
