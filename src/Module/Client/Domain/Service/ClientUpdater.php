<?php

namespace App\Module\Client\Domain\Service;

use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\Client\Domain\Service\Authorization\ClientPermissionVerifier;
use App\Module\Client\Repository\ClientUpdaterRepository;
use App\Module\Exception\Domain\InvalidOperationException;
use App\Module\User\Enum\UserActivity;
use App\Module\UserActivity\Service\UserActivityLogger;

final readonly class ClientUpdater
{
    public function __construct(
        private ClientUpdaterRepository $clientUpdaterRepository,
        private ClientValidator $clientValidator,
        private ClientFinder $clientFinder,
        private ClientPermissionVerifier $clientPermissionVerifier,
        private UserActivityLogger $userActivityLogger,
    ) {
    }

    /**
     * Change client values.
     *
     * @param int $clientId id of client being changed
     * @param array $clientValues values that user wants to change
     *
     * @return array update infos containing status and optionally other
     */
    public function updateClient(int $clientId, array $clientValues): array
    {
        // Working with array and not ClientData object to be able to differentiate values that user wants to set to null
        $this->clientValidator->validateClientValues($clientValues, false);

        // Find client in db to verify its ownership
        $clientFromDb = $this->clientFinder->findClient($clientId, true);

        // Check if the user is granted to update client
        if (!$this->clientPermissionVerifier->isGrantedToUpdate($clientValues, $clientFromDb->userId)) {
            // NOT ALLOWED - add activity entry with failed update attempt
            $this->userActivityLogger->logUserActivity(
                UserActivity::UPDATED,
                'client',
                $clientId,
                array_merge(['status' => 'FAILED'], $clientValues)
            );
            throw new ForbiddenException('Not allowed to update client.');
        }

        $updateData = [];
        $responseData = null;
        // Check to be sure that only columns that may be updated are in the final $updateData array
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
                throw new InvalidOperationException('Not allowed to change client column ' . $column);
            }
        }
        // If birthdate is set, change the format to suit database
        if (isset($updateData['birthdate'])) {
            if (empty($updateData['birthdate'])) {
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
            // If client is undeleted, the notes should also be restored
            if (array_key_exists('deleted_at', $updateData) && $updateData['deleted_at'] === null) {
                $this->clientUpdaterRepository->restoreNotesFromClient($clientId, $clientFromDb->deletedAt);
            }

            $this->userActivityLogger->logUserActivity(
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
}
