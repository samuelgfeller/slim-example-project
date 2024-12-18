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
 * This function is used for text translation.
 * It takes a message string and an optional context.
 * The message is passed to the gettext function for translation.
 * If a context is provided, it is used to replace placeholders
 * in the translated string.
 *
 * @param string $message the message to be translated (may contain sprintf placeholders e.g. %s, %d)
 * @param mixed ...$context Optional elements that should be inserted in the string with placeholders.
 * The function can be called like this:
 * __('The %s contains %d monkeys and %d birds.', 'tree', 5, 3);
 * With the argument unpacking operator ...$context, the arguments are accessible within the function as an array.
 *
 * @return string the translated string
 */
function __(string $message, ...$context): string
{
    $translated = gettext($message);
    if (!empty($context)) {
        // If context is provided, replace placeholders in the translated string
        $translated = vsprintf($translated, $context);
    }

    return $translated;
}
