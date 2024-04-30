<?php

namespace App\Domain\User\Enum;

enum UserActivity: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case READ = 'read';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::CREATED => __('created'),
            self::UPDATED => __('updated'),
            self::DELETED => __('deleted'),
            self::READ => __('read'),
        };
    }
}
