<?php

namespace App\Domain\Client\Enum;

use App\Common\Trait\EnumToArray;

enum ClientVigilanceLevel: string
{
    use EnumToArray;

    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    /**
     * Get the enum case name that can be displayed by the frontend.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::LOW => __('Low'),
            self::MEDIUM => __('Medium'),
            self::HIGH => __('High'),
        };
    }
}
