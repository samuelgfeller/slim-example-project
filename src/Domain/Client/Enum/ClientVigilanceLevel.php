<?php

namespace App\Domain\Client\Enum;

use App\Common\Trait\EnumToArray;

enum ClientVigilanceLevel: string
{
    use EnumToArray;

    case MODERATE = 'moderate';
    case CAUTION = 'caution';
    case EXTRA_CAUTION = 'extra_caution';

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
            __('Moderate'),
            __('Caution'),
            __('Extra caution'),
        ];
    }

    /**
     * All letters lowercase except first capital letter
     * and replaces underscores with spaces.
     *
     * Would love this function to be global / be in a trait that could be used
     * but don't know the best way to implement it right now as there is no access
     * to "this" in a trait for instance
     *
     * @return string
     */
    public function prettyName(): string
    {
        // Resulting string is a key in the function getTranslatedValues so __() knows how to translate
        return __(str_replace('_', ' ', ucfirst(mb_strtolower($this->value))));
    }
}
