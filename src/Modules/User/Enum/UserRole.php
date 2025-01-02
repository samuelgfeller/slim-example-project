<?php

namespace App\Modules\User\Enum;

use App\Core\Shared\Trait\EnumToArray;

enum UserRole: string
{
    use EnumToArray;

    // Value is db column `user_role`.`name`
    case NEWCOMER = 'newcomer';
    case ADVISOR = 'advisor';
    case MANAGING_ADVISOR = 'managing_advisor';
    case ADMIN = 'admin';

    /**
     * Returns the enum case name that can be displayed by the frontend.
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
