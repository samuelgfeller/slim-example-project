<?php

namespace App\Common;

use App\Domain\Utility\Settings;

final class LocaleHelper
{
    private array $localeSettings;

    public function __construct(Settings $settings)
    {
        $this->localeSettings = $settings->get('locale');
    }

    /**
     * Set the locale to the given lang code and bind text domain for gettext translations.
     *
     * @param string|false|null $locale The locale or language code (e.g. 'en_US' or 'en')
     * @param string $domain The text domain (e.g. 'messages')
     *
     * @return false|string
     */
    public function setLanguage(string|null|false $locale, string $domain = 'messages'): bool|string
    {
        $codeset = 'UTF-8';
        $defaultLocale = $this->localeSettings['default'] ?? 'en_US';
        // Current path src/Application/Middleware
        $directory = __DIR__ . '/../../resources/translations';
        // If locale has hyphen instead of underscore, replace it
        $locale = $locale ? str_replace('-', '_', $locale) : $defaultLocale;
        // Get language code from locale
        $langCode = explode('_', $locale)[0];
        // Get the available locale or the default one
        $locale = $this->localeSettings['available'][$langCode] ?? $defaultLocale;
        // Get locale with hyphen as alternative if server doesn't have the one with underscore (windows)
        $localeHyphen = str_replace('_', '-', $locale);

        // Set locale information
        $setLocaleResult = setlocale(LC_ALL, $locale, $localeHyphen);
        // Check for existing mo file (optional)
        $file = sprintf('%s/%s/LC_MESSAGES/%s_%s.mo', $directory, $locale, $domain, $locale);
        if ($locale !== 'en_US' && !file_exists($file)) {
            throw new \UnexpectedValueException(sprintf('File not found: %s', $file));
        }
        // Generate new text domain
        $textDomain = sprintf('%s_%s', $domain, $locale);
        // Set base directory for all locales
        bindtextdomain($textDomain, $directory);
        // Set domain codeset
        bind_textdomain_codeset($textDomain, $codeset);
        textdomain($textDomain);

        return $setLocaleResult;
    }

    /**
     * Returns the current language code of the set locale with an
     * added slash "/" at the end of the string if not empty.
     *
     * @return string language code or empty string if default or language code not found
     */
    public function getLanguageCodeForPath(): string
    {
        // Get the key of the current locale
        $localeCode = setlocale(LC_ALL, 0);
        // Available locales keys are language codes ('en', 'de') and values are locale codes ('en_US', 'de_CH')
        $langCode = array_search($localeCode, $this->localeSettings['available'], true) ?: '';

        // If language code is 'en' return empty string as default email templates are in english and not in a sub folder
        // If language code is not empty, add a slash to complete the path it will be inserted into
        return $langCode === 'en' || $langCode === '' ? '' : $langCode . '/';
    }
}
