<?php

namespace App\Domain\ClientStatus\Data;

use App\Common\ArrayReader;

class ClientStatusData
{
    public ?int $id;
    public ?string $name;

    public function __construct(array $statusData)
    {
        $reader = new ArrayReader($statusData);
        $this->id = $reader->findAsInt('id');
        $this->name = $reader->findAsString('name');
    }
}