<?php

namespace App\Domain\User\Enum;

enum UserActivity: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case READ = 'read';

    /**
     * Calling the translation function __() for each enum value
     * so that poedit recognizes them to be translated.
     * When using the enum values, __() will work anywhere as it's
     * setup here and translations are in the .mo files.
     *
     * @return array
     */
    private function getTranslatedValues(): array
    {
        return [
            __('created'),
            __('updated'),
            __('deleted'),
            __('read'),
        ];
    }
}
