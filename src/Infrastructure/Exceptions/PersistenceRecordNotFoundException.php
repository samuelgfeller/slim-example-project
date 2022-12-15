<?php

declare(strict_types = 1);

namespace App\Infrastructure\Exceptions;

class PersistenceRecordNotFoundException extends PersistenceException
{
    public $message = 'The requested entry does not exist.';

    public function __construct(string $tableName)
    {
        parent::__construct('The requested ' . $tableName . ' does not exist.');
    }
}
