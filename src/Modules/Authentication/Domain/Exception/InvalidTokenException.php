<?php

namespace App\Modules\Authentication\Domain\Exception;

use App\Modules\User\Data\UserData;

class InvalidTokenException extends \RuntimeException
{
    public UserData $userData;

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
