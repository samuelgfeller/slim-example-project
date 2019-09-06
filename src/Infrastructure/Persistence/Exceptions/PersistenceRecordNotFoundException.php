<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Exceptions;

class PersistenceRecordNotFoundException extends PersistenceException
{
    public $message = 'The requested entry does not exist.';
    
    public function setNotFoundElement($tableName)
    {
        $this->message = 'The requested '.$tableName.' does not exist.';
    }
}
