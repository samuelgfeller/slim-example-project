<?php

namespace App\Core\Common\Trait;

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

    /**
     * Creates an array with the enum values as keys and the translated names as values.
     * Requires the getDisplayName method to be implemented in the enum.
     *
     * @return array
     */
    public static function getAllDisplayNames(): array
    {
        // Creates an array by using one array for keys and another for its values
        return array_combine(self::values(), self::getDisplayNamesArray(self::cases()));
    }

    /**
     * Returns array with all enum values as names that can be displayed by the frontend.
     * Requires the getDisplayName() method to be implemented in the enum.
     *
     * @param self[] $enumCases
     *
     * @return array
     */
    private static function getDisplayNamesArray(array $enumCases): array
    {
        $displayNames = [];
        foreach ($enumCases as $enumCase) {
            // If the enum case has a getDisplayName method, use it to get the display name to otherwise use the case name
            /** @phpstan-ignore-next-line https://github.com/phpstan/phpstan/issues/7599 */
            if (method_exists($enumCase, 'getDisplayName')) {
                $displayNames[] = $enumCase->getDisplayName();
            } else {
                $displayNames[] = $enumCase->name;
            }
        }

        return $displayNames;
    }
}
