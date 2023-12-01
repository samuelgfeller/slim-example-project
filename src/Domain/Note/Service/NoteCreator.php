<?php

namespace App\Domain\Note\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Note\Repository\NoteCreatorRepository;
use App\Domain\Note\Service\Authorization\NotePermissionVerifier;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Service\UserFinder;
use App\Domain\UserActivity\Service\UserActivityLogger;
use IntlDateFormatter;

class NoteCreator
{
    public function __construct(
        private readonly NoteValidator $noteValidator,
        private readonly NoteCreatorRepository $noteCreatorRepository,
        private readonly NotePermissionVerifier $notePermissionVerifier,
        private readonly UserActivityLogger $userActivityLogger,
        private readonly UserNetworkSessionData $userNetworkSessionData,
        private readonly UserFinder $userFinder,
        private readonly NoteFinder $noteFinder
    ) {
    }

    /**
     * Note creation logic.
     *
     * @param array $noteValues
     *
     * @return array note insert id, user full name and note created at timestamp
     */
    public function createNote(array $noteValues): array
    {
        $noteValues['user_id'] = $this->userNetworkSessionData->userId;
        // Exception thrown if validation fails
        $this->noteValidator->validateNoteValues($noteValues, true);

        if ($this->notePermissionVerifier->isGrantedToCreate((int)$noteValues['is_main'])) {
            $noteId = $this->noteCreatorRepository->insertNote($noteValues);
            if (!empty($noteId)) {
                $this->userActivityLogger->logUserActivity(
                    UserActivity::CREATED,
                    'note',
                    $noteId,
                    $noteValues
                );
            }

            // Retrieve data that will be sent to client after note creation
            $user = $this->userFinder->findUserById($this->userNetworkSessionData->userId);
            $noteCreatedAtTimestamp = $this->noteFinder->findNote($noteId)->createdAt ?: time();
            $dateFormatter = new IntlDateFormatter(
                setlocale(LC_ALL, 0) ?: null,
                IntlDateFormatter::LONG,
                IntlDateFormatter::SHORT
            );

            return [
                'note_id' => $noteId,
                'user_full_name' => $user->firstName . ' ' . $user->surname,
                'formatted_creation_timestamp' => $dateFormatter->format($noteCreatedAtTimestamp),
            ];
        }
        throw new ForbiddenException(__(sprintf('Not allowed to create %s', __('note'))));
    }
}
