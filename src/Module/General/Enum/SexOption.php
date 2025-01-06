<?php

namespace App\Module\General\Enum;

use App\Core\Shared\Trait\EnumToArray;

enum SexOption: string
{
    use EnumToArray;

    case MALE = 'M';
    case FEMALE = 'F';
    case OTHER = 'O';
    // Cannot have null as it is displayed in the create form as radio buttons
    // case NULL = '';

    /**
     * Returns the enum case name that can be displayed by the frontend.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::MALE => __('Male'),
            self::FEMALE => __('Female'),
            self::OTHER => __('Other'),
        };
    }
}
