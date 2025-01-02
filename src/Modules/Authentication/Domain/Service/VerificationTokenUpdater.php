<?php

namespace App\Modules\Authentication\Domain\Service;

use App\Modules\Authentication\Repository\VerificationToken\VerificationTokenUpdaterRepository;
use App\Modules\User\Enum\UserActivity;
use App\Modules\UserActivity\Service\UserActivityLogger;

final readonly class VerificationTokenUpdater
{
    public function __construct(
        private VerificationTokenUpdaterRepository $verificationTokenUpdaterRepository,
        private UserActivityLogger $userActivityLogger,
    ) {
    }

    /**
     * Set the verification token to "used".
     *
     * @param int $verificationId
     * @param int|null $userId
     *
     * @return bool
     */
    public function setVerificationEntryToUsed(int $verificationId, ?int $userId): bool
    {
        $updateValues = ['used_at' => (new \DateTime())->format('Y-m-d H:i:s')];
        $success = $this->verificationTokenUpdaterRepository->updateUserVerificationRow($verificationId, $updateValues);
        if ($success) {
            // Add user activity entry
            $this->userActivityLogger->logUserActivity(
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
