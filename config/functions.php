<?php

/**
 * Autoload functions available everywhere across the application.
 * Documentation: https://samuel-gfeller.ch/docs/Composer#autoload.
 *
 * @param ?string $text
 */

/**
 * Convert all applicable characters to HTML entities.
 *
 * @param string|null $text The string
 *
 * @return string The html encoded string
 */
function html(?string $text = null): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Translate a message string using gettext with optional placeholder replacement.
 *
 * This function serves as a wrapper around gettext() for localization (i18n).
 * It supports sprintf-style placeholders for dynamic content insertion.
 *
 * Example usage:
 * ```php
 * __('The %s contains %d monkeys and %d birds.', 'tree', 5, 3);
 * // Returns: "The tree contains 5 monkeys and 3 birds."
 * ```
 *
 * @param string|null $message The message to be translated. May contain sprintf placeholders
 *                             (e.g., %s for strings, %d for integers). Null returns empty string.
 * @param mixed ...$context Optional values to replace placeholders in the translated string.
 *                         Values are passed to vsprintf() in the order provided.
 *
 * @return string The translated string with placeholders replaced, or empty string if the message is null
 */
function __(?string $message, ...$context): string
{
    if ($message === null) {
        return '';
    }
    $translated = gettext($message);
    if (!empty($context)) {
        // If context is provided, replace placeholders in the translated string
        $translated = vsprintf($translated, $context);
    }

    return $translated;
}

/**
 * Generate a key-value array of translations for frontend use.
 *
 * Converts an array of English strings into an associative array where each key
 * is the original English string and its value is the translated equivalent.
 * This format is particularly useful for JavaScript internationalization.
 *
 * Example:
 * ```php
 * $translations = addTranslationsArray(['Hello', 'Goodbye']);
 * // Returns: ['Hello' => 'Hola', 'Goodbye' => 'Adiós'] (if Spanish is the  active locale)
 * ```
 *
 * @param array $translations Array of English strings to be translated
 *
 * @return string json encoded Associative array with English strings as keys and translations as values
 */
function addTranslationsArray(array $translations = []): string
{
    $translatedArray = [];
    foreach ($translations as $message) {
        $translatedArray[$message] = __($message);
    }

    return json_encode($translatedArray) ?: '';
}
