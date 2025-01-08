<?php

namespace App\Module\Client\Domain\Service;

use App\Module\Authorization\Enum\Privilege;
use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\Client\Authorization\Service\ClientPermissionVerifier;
use App\Module\Client\Authorization\Service\ClientPrivilegeDeterminer;
use App\Module\Client\Data\ClientData;
use App\Module\Client\Read\Data\ClientReadResult;
use App\Module\Client\Repository\ClientFinderRepository;
use App\Module\Note\Domain\Service\Authorization\NotePermissionVerifier;
use App\Module\Note\Domain\Service\Authorization\NotePrivilegeDeterminer;
use App\Module\Note\Repository\NoteFinderRepository;

final readonly class ClientFinder
{
    public function __construct(
        private ClientFinderRepository $clientFinderRepository,
        private NoteFinderRepository $noteFinderRepository,
        private ClientPermissionVerifier $clientPermissionVerifier,
        private ClientPrivilegeDeterminer $clientPrivilegeDeterminer,
        private NotePrivilegeDeterminer $notePrivilegeDeterminer,
        private NotePermissionVerifier $notePermissionVerifier,
    ) {
    }

    /**
     * Find one client in the database.
     *
     * @param int $id
     * @param bool $includeDeleted
     *
     * @return ClientData
     */
    public function findClient(int $id, bool $includeDeleted = false): ClientData
    {
        return $this->clientFinderRepository->findClientById($id, $includeDeleted);
    }

    /**
     * Find one client in the database with aggregate (main note, assigned user, status, privileges, notes amount).
     *
     * @param int $clientId
     *
     * @return ClientReadResult
     */
    public function findClientReadAggregate(int $clientId): ClientReadResult
    {
        $clientResultAggregate = $this->clientFinderRepository->findClientAggregateByIdIncludingDeleted($clientId);
        if ($clientResultAggregate->id
            && $this->clientPermissionVerifier->isGrantedToRead(
                $clientResultAggregate->userId,
                $clientResultAggregate->deletedAt
            )
        ) {
            // Set client mutation privilege
            $clientResultAggregate->generalPrivilege = $this->clientPrivilegeDeterminer->getMutationPrivilege(
                $clientResultAggregate->userId,
                'personal_info'
            );
            // Set main note privilege
            if ($clientResultAggregate->mainNoteData !== null) {
                $clientResultAggregate->mainNoteData->privilege = $this->notePrivilegeDeterminer->getMainNotePrivilege(
                    $clientResultAggregate->mainNoteData->userId,
                    $clientResultAggregate->userId
                );
            }

            // Set assigned user privilege
            $clientResultAggregate->assignedUserPrivilege = $this->clientPrivilegeDeterminer->getMutationPrivilege(
                $clientResultAggregate->userId,
                'user_id',
            );
            //  Set client status privilege
            $clientResultAggregate->clientStatusPrivilege = $this->clientPrivilegeDeterminer->getMutationPrivilege(
                $clientResultAggregate->userId,
                'client_status_id',
            );
            //  Set create note privilege
            $clientResultAggregate->noteCreationPrivilege = $this->notePermissionVerifier->isGrantedToCreate(
                0,
                $clientResultAggregate->userId,
                false
            ) ? Privilege::CR->name : Privilege::N->name;

            $clientResultAggregate->notesAmount = $this->noteFinderRepository->findClientNotesAmount($clientId);

            return $clientResultAggregate;
        }
        // The reasons this exception is thrown when tried to access soft deleted clients:
        // they are supposed to be deleted, so only maybe a very high privileged role should have access, and it should
        // be marked as deleted in the GUI as well. Also, a non-authorized user trying to access a client
        // should not be able to distinguish which clients exist and which are not so for both cases the
        // not allowed exception should be thrown.
        throw new ForbiddenException('Not allowed to read client.');
    }
}
