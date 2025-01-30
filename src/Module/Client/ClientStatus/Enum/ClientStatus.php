<?php

namespace App\Module\Client\ClientStatus\Enum;

enum ClientStatus: string
{
    case ACTION_PENDING = 'Action pending';
    case IN_CARE = 'In care';
    case HELPED = 'Helped';
    case CANNOT_HELP = 'Cannot help';

    /**
     * Returns the enum case name that can be displayed by the frontend.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::ACTION_PENDING => __('Action pending'),
            self::IN_CARE => __('In care'),
            self::HELPED => __('Helped'),
            self::CANNOT_HELP => __('Cannot help'),
        };
    }
}
