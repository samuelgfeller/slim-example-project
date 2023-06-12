<?php

namespace App\Domain\Client\Enum;

enum ClientStatus: string
{
    case ACTION_PENDING = 'Action pending';
    case IN_CARE = 'In care';
    case HELPED = 'Helped';
    case CANNOT_HELP = 'Cannot help';

    /**
     * Calling the translation function __() for each enum value
     * so that poedit recognizes them to be translated.
     * When using the enum values, __() will work as it's
     * setup here and translations are in the .mo files.
     *
     * @return array
     */
    private function getTranslatedValues(): array
    {
        return [
            __('Action pending'),
            __('In care'),
            __('Helped'),
            __('Cannot help'),
        ];
    }
}
