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

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        return array_combine(self::values(), self::names());
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
            $prettyNames[] = str_replace('_', ' ', ucfirst(mb_strtolower($name)));
        }

        return $prettyNames;
    }
}
