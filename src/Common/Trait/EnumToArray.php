<?php

namespace App\Common\Trait;

/**
 * Source: https://stackoverflow.com/a/71680007/9013718.
 */
trait EnumToArray
{
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function translatedNames(): array
    {
        // Run the translation function __() over each name
        return array_map('__', self::names());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        return array_combine(self::values(), self::names());
    }

    public static function toTranslatedNamesArray(): array
    {
        // Returns enum cases with value as key and translated name as value
        return array_combine(self::values(), self::translatedNames());
    }

    public static function toArrayWithPrettyNames(): array
    {
        return array_combine(self::values(), self::prettifyNames(self::names()));
    }

    /**
     * All letters lowercase except first capital letter
     * and replaces underscores with spaces.
     *
     * @param array $names
     *
     * @return array
     */
    private static function prettifyNames(array $names): array
    {
        $prettyNames = [];
        foreach ($names as $name) {
            // String is a key in the enum function getTranslatedValues so __() knows how to translate
            $prettyNames[] = __(str_replace('_', ' ', ucfirst(mb_strtolower($name))));
        }

        return $prettyNames;
    }
}
