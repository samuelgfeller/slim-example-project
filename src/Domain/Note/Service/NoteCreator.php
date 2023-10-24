<?php

namespace App\Domain\Note\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Authorization\NoteAuthorizationChecker;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Service\UserActivityManager;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\Note\NoteCreatorRepository;
use IntlDateFormatter;

class NoteCreator
{
    public function __construct(
        private readonly NoteValidator $noteValidator,
        private readonly NoteCreatorRepository $noteCreatorRepository,
        private readonly NoteAuthorizationChecker $noteAuthorizationChecker,
        private readonly UserActivityManager $userActivityManager,
        private readonly UserNetworkSessionData $userNetworkSessionData,
        private readonly UserFinder $userFinder,
        private readonly NoteFinder $noteFinder,
        LoggerFactory $logger
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

        if ($this->noteAuthorizationChecker->isGrantedToCreate((int)$noteValues['is_main'])) {
            $noteId = $this->noteCreatorRepository->insertNote($noteValues);
            if (!empty($noteId)) {
                $this->userActivityManager->addUserActivity(
                    UserActivity::CREATED,
                    'note',
                    $noteId,
                    $noteValues
                );
            }

            // Retrieve data that will be sent to client after note creation
            $user = $this->userFinder->findUserById($this->userNetworkSessionData->userId);
            $noteCreatedAtTimestamp = $this->noteFinder->findNote($noteId)->createdAt;
            $dateFormatter = new IntlDateFormatter(
                setlocale(LC_ALL, 0),
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
