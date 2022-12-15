<?php

namespace App\Domain\Authentication\Service;

use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Service\UserActivityManager;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenUpdaterRepository;

class VerificationTokenUpdater
{
    public function __construct(
        private readonly VerificationTokenUpdaterRepository $verificationTokenUpdaterRepository,
        private readonly UserActivityManager $userActivityManager,
    ) {
    }

    /**
     * Set verification token to used.
     *
     * @param int $verificationId
     * @param int $userId
     *
     * @return bool
     */
    public function setVerificationEntryToUsed(int $verificationId, int $userId): bool
    {
        $updateValues = ['used_at' => (new \DateTime())->format('Y-m-d H:i:s')];
        $success = $this->verificationTokenUpdaterRepository->updateUserVerificationRow($verificationId, $updateValues);
        if ($success) {
            // Add user activity entry
            $this->userActivityManager->addUserActivity(
                UserActivity::UPDATED,
                'user_verification',
                $verificationId,
                $updateValues,
                $userId
            );
        }

        return $success;
    }
}
