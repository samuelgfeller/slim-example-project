<?php

namespace App\Domain\Client\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Exception\NotAllowedException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Service\UserActivityManager;
use App\Infrastructure\Client\ClientUpdaterRepository;
use Psr\Log\LoggerInterface;

class ClientUpdater
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly ClientUpdaterRepository $clientUpdaterRepository,
        private readonly ClientValidator $clientValidator,
        private readonly ClientFinder $clientFinder,
        LoggerFactory $logger,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
        private readonly UserActivityManager $userActivityManager,
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('post-service');
    }

    /**
     * Change something or multiple things on post.
     *
     * @param int $clientId id of post being changed
     * @param array|null $clientValues values that user wants to change
     * @param int $loggedInUserId
     *
     * @return array update infos containing status and optionally other
     */
    public function updateClient(int $clientId, null|array $clientValues, int $loggedInUserId): array
    {
        // Working with array and not ClientData object to be able to differentiate values that user wants to set to null
        $this->clientValidator->validateClientUpdate($clientValues);

        // Find note in db to compare its ownership
        $clientFromDb = $this->clientFinder->findClient($clientId);

        if ($this->clientAuthorizationChecker->isGrantedToUpdate($clientValues, $clientFromDb->userId)) {
            $updateData = [];
            $responseData = null;
            // Additional check (next to malformed body in action) to be sure that only columns that may be updated are
            // in the final $updateData array
            foreach ($clientValues as $column => $value) {
                // Check that keys are one of the database columns that may be updated
                if (in_array($column, [
                    'client_status_id',
                    'user_id',
                    'first_name',
                    'last_name',
                    'phone',
                    'location',
                    'birthdate',
                    'email',
                    'sex',
                    'vigilance_level',
                    'deleted_at',
                ], true)) {
                    // If $value is an empty string, change it to null
                    if ($value === '') {
                        $value = null;
                    }
                    $updateData[$column] = $value;
                } else {
                    throw new NotAllowedException('Not allowed to change client column ' . $column);
                }
            }
            // If birthdate is set, change the format to suit database
            if (isset($updateData['birthdate'])) {
                if ($updateData['birthdate'] === '') {
                    $updateData['birthdate'] = null;
                } else {
                    $birthdate = new \DateTime($updateData['birthdate']);
                    // Change datetime format to database
                    $updateData['birthdate'] = $birthdate->format('Y-m-d');
                    $responseData['age'] = (new \DateTime())->diff($birthdate)->y;
                }
            }
            // If assigned to user add assigned_at date as well
            if (isset($updateData['user_id'])) {
                $updateData['assigned_at'] = date('Y-m-d H:i:s');
            }
            // Update client
            $updated = $this->clientUpdaterRepository->updateClient($updateData, $clientId);
            if ($updated) {
                $this->userActivityManager->addUserActivity(
                    UserActivity::UPDATED,
                    'client',
                    $clientId,
                    $updateData
                );
            }

            return [
                'updated' => $updated,
                'data' => $responseData,
            ];
        }

        // User does not have needed rights to access area or function
        $this->logger->notice(
            'User ' . $loggedInUserId . ' tried to update client with id: ' . $clientId .
            ' but isn\'t allowed.'
        );
        // Add activity entry with failed update attempt
        $this->userActivityManager->addUserActivity(
            UserActivity::UPDATED,
            'client',
            $clientId,
            array_merge(['status' => 'FAILED'], $clientValues)
        );
        throw new ForbiddenException('Not allowed to change that client.');
    }
}
