<?php


namespace App\Domain\Note\Data;

use App\Common\ArrayReader;
use App\Domain\Authorization\Privilege;

/**
 * Note with user info
 * todo evaluate if not better as extended class from UserData
 */
class NoteWithUserData
{
    public ?int $noteId;
    public ?string $noteMessage;
    public ?int $noteHidden;
    public ?string $noteCreatedAt;
    public ?string $noteUpdatedAt;
    public ?int $userId;
    public ?string $userFullName;

    // Not note value from db, populated in NoteUserRightSetter
    public ?Privilege $privilege; // json_encode automatically takes $enum->value

    /**
     * Note constructor.
     * @param array $noteData
     */
    public function __construct(array $noteData = [])
    {
        $arrayReader = new ArrayReader($noteData);
        $this->noteId = $arrayReader->findAsInt('note_id');
        $this->userId = $arrayReader->findAsInt('user_id');
        $this->noteMessage = $arrayReader->findAsString('note_message');
        $this->noteHidden = $arrayReader->findAsInt('note_hidden');
        $this->noteCreatedAt = $arrayReader->findAsString('note_created_at');
        $this->noteUpdatedAt = $arrayReader->findAsString('note_updated_at');
        $this->userFullName = $arrayReader->findAsString('user_full_name');
    }
}