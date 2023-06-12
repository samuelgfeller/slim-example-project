<?php

namespace App\Domain\User\Enum;

enum UserRole: string
{
    // Value is `user_role`.`name`
    case NEWCOMER = 'newcomer';
    case ADVISOR = 'advisor';
    case MANAGING_ADVISOR = 'managing_advisor';
    case ADMIN = 'admin';

    /**
     * Removes underscore and adds capital first letter.
     *
     * @return string
     */
    public function roleNameForDropdown(): string
    {
        // Resulting string is a key in the function getTranslatedValues so __() knows how to translate
        return __(ucfirst(str_replace('_', ' ', $this->value)));
    }

    /**
     * Setup keys that should be translated used by the function above.
     *
     * @return array
     */
    public static function getTranslatedValues(): array
    {
        return [
            __('Newcomer'),
            __('Advisor'),
            __('Managing advisor'),
            __('Admin'),
        ];
    }
}
