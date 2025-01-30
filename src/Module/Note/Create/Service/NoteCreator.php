<?php

namespace App\Module\Note\Create\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\Note\Create\Repository\NoteCreatorRepository;
use App\Module\Note\Find\Service\NoteFinder;
use App\Module\Note\Validation\Service\NoteValidator;
use App\Module\User\Enum\UserActivity;
use App\Module\User\Find\Service\UserFinder;
use App\Module\UserActivity\Create\Service\UserActivityLogger;
use IntlDateFormatter;

final readonly class NoteCreator
{
    public function __construct(
        private NoteValidator $noteValidator,
        private NoteCreatorRepository $noteCreatorRepository,
        private NoteCreateAuthorizationChecker $noteCreateAuthorizationChecker,
        private UserActivityLogger $userActivityLogger,
        private UserNetworkSessionData $userNetworkSessionData,
        private UserFinder $userFinder,
        private NoteFinder $noteFinder,
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

        if ($this->noteCreateAuthorizationChecker->isGrantedToCreate((int)$noteValues['is_main'])) {
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
                'user_full_name' => $user->firstName . ' ' . $user->lastName,
                'formatted_creation_timestamp' => $dateFormatter->format($noteCreatedAtTimestamp),
            ];
        }
        throw new ForbiddenException(__('Not allowed to create %s', __('note')));
    }
}
