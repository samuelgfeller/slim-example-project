<?php
declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Exceptions\DomainRecordNotFoundException;

class UserNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'The user you requested does not exist.';
}
