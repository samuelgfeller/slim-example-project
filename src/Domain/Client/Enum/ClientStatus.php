<?php

namespace App\Domain\Client\Enum;

enum ClientStatus: string
{
    case ACTION_PENDING = 'Action pending';
    case IN_CARE = 'In care';
    case HELPED = 'Helped';
    case CANNOT_HELP = 'Cannot help';

    /**
     * This function is not designed to be used.
     * In order for the enum values to be acknowledged by the
     * translation tool Poedit, they each
     * have to be called with the __() method.
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
