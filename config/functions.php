<?php

/**
 * Convert all applicable characters to HTML entities.
 *
 * @param string|null $text The string
 *
 * @return string The html encoded string
 */
function html(string $text = null): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Get validation error message of given field.
 *
 * @param array $validation Entire validation array (containing message and errors)
 * @param string $field Name given in validation class where error messages are made (may differ from HTML)
 *
 * @return string|null
 */
function get_field_error(array $validation, string $field): string|null
{
    if ($validation !== []) {
        $key = array_search($field, array_column($validation['errors'], 'field'), false);
        if ($key !== false) {
            return $validation['errors'][$key]['message'];
        }
    }

    return null;
}

/**
 * Text translation.
 *
 * @param string $message The message
 * @param string|int|float|bool ...$context The context
 *
 * @return string The translated string
 */
function __(string $message, ...$context): string
{
    $locale = setlocale(LC_ALL, 0);
    $translated = gettext($message);
    if (!empty($context)) {
        $translated = vsprintf($translated, $context);
    }

    return $translated;
}

/**
 * Set locale.
 *
 * @param string $locale The locale (en_US)
 * @param string $domain The text domain (messages)
 *
 * @throws UnexpectedValueException
 *
 * @retrun void
 */
function set_language(string $locale, string $domain = 'messages'): void
{
    $codeset = 'UTF-8';
    $directory = __DIR__ . '/../resources/translations';
    // Set locale information
    $localeHyphen = str_replace('_', '-', $locale);
    setlocale(LC_ALL, $locale, $localeHyphen);
    // Check for existing mo file (optional)
    $file = sprintf('%s/%s/LC_MESSAGES/%s_%s.mo', $directory, $locale, $domain, $locale);
    if ($locale !== 'en_US' && !file_exists($file)) {
        throw new UnexpectedValueException(sprintf('File not found: %s', $file));
    }
    // Generate new text domain
    $textDomain = sprintf('%s_%s', $domain, $locale);
    // Set base directory for all locales
    bindtextdomain($textDomain, $directory);
    // Set domain codeset
    bind_textdomain_codeset($textDomain, $codeset);
    textdomain($textDomain);
}
