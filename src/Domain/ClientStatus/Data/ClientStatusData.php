<?php

namespace App\Domain\ClientStatus\Data;

class ClientStatusData
{
    public ?int $id;
    public ?string $name;

    public function __construct(array $statusData = [])
    {
        $this->id = $statusData['id'] ?? null;
        $this->name = $statusData['name'] ?? null;
    }
}
