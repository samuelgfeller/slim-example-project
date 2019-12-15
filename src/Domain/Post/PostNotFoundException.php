<?php
declare(strict_types=1);

namespace App\Domain\Post;

use App\Domain\DomainException\DomainRecordNotFoundException;

class PostNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'The post you requested does not exist.';
}
