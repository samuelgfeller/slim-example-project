<?php

namespace App\Domain\User\Enum;

use App\Common\Trait\EnumToArray;

enum UserRole: string
{
    use EnumToArray;

    // Value is `user_role`.`name`
    case NEWCOMER = 'newcomer';
    case ADVISOR = 'advisor';
    case MANAGING_ADVISOR = 'managing_advisor';
    case ADMIN = 'admin';

    /**
     * Get the enum case name that can be displayed by the frontend.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::NEWCOMER => __('Newcomer'),
            self::ADVISOR => __('Advisor'),
            self::MANAGING_ADVISOR => __('Managing advisor'),
            self::ADMIN => __('Admin'),
        };
    }
}
