<?php

namespace App\Module\Exception\Infrastructure;

class PersistenceRecordNotFoundException extends PersistenceException
{
    public function __construct(string $tableName)
    {
        parent::__construct('The requested ' . $tableName . ' does not exist.');
    }
}
