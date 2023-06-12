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
     * Calling the translation function __() for each enum value
     * so that poedit recognizes them to be translated.
     * When using the enum values, __() will work as it's
     * setup here and translations are in the .mo files.
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
}
