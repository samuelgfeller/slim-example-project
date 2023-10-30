<?php

namespace App\Domain\Note\Data;

use App\Domain\Authorization\Privilege;

/**
 * Note with user and client full name and privilege.
 */
class NoteResultData extends NoteData
{
    public ?string $userFullName;
    public ?string $clientFullName;
    public ?bool $isClientMessage = false;

    // Populated in NoteUserRightSetter
    public ?Privilege $privilege; // json_encode automatically takes $enum->value


    public function __construct(array $noteValues = [])
    {
        parent::__construct($noteValues);

        $this->userFullName = $noteValues['user_full_name'] ?? null;
        $this->clientFullName = $noteValues['client_full_name'] ?? null;
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'userFullName' => $this->userFullName,
            'clientFullName' => $this->clientFullName,
            'privilege' => $this->privilege->value,
            'isClientMessage' => (int)$this->isClientMessage,
        ]);
    }
}
