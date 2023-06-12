<?php

namespace App\Domain\General\Enum;

use App\Common\Trait\EnumToArray;

enum SexOption: string
{
    use EnumToArray;

    case MALE = 'M';
    case FEMALE = 'F';
    case OTHER = 'O';
    // Cannot have null as it is displayed in the create form as radio buttons
    // case NULL = '';

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
            __('Male'),
            __('Female'),
            __('Other'),
        ];
    }
}
