<?php

namespace App\Infrastructure\Service;

use App\Infrastructure\Utility\Settings;

final class LocaleConfigurator
{
    private array $localeSettings;

    public function __construct(Settings $settings)
    {
        $this->localeSettings = $settings->get('locale');
    }

    /**
     * Sets the locale and language settings for the application.
     *
     * @param string|null|false $locale The locale or language code (e.g. 'en_US' or 'en').
     * If null or false, the default locale from the settings is used.
     * @param string $domain The text domain (default 'messages') for gettext translations.
     *
     * @return false|string The new locale string, or false on failure.
     *
     * @throws \UnexpectedValueException If the locale is not 'en_US' and no translation file exists for the locale.
     */
    public function setLanguage(string|null|false $locale, string $domain = 'messages'): bool|string
    {
        $codeset = 'UTF-8';
        $defaultLocale = $this->localeSettings['default'] ?? 'en_US';
        $directory = $this->localeSettings['translations_path'];
        // If locale has hyphen instead of underscore, replace it
        $locale = $locale && str_contains($locale, '-') ? str_replace('-', '_', $locale) : $locale;
        // Get an available locale. Either input locale, the locale for another region or default
        $locale = $this->getAvailableLocale($locale);

        // Get locale with hyphen as an alternative if server doesn't have the one with underscore (windows)
        $localeWithHyphen = str_replace('_', '-', $locale);

        // Set locale information
        $setLocaleResult = setlocale(LC_ALL, $locale, $localeWithHyphen);
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
     * When using this function, a subdirectory for the language has to exist in templates.
     *
     * @return string language code or empty string if default or language code not found
     */
    public function getLanguageCodeForPath(): string
    {
        // Get the key of the current locale which has to be an available locale
        $currentLocale = setlocale(LC_ALL, 0);
        $langCode = $this->getLanguageCodeFromLocale($currentLocale);

        // If language code is 'en' return an empty string as the default email templates are in english and not in a
        // subdirectory.
        // If language code is not empty, add a slash to complete the path it will be inserted into
        return $langCode === 'en' || $langCode === '' ? '' : $langCode . '/';
    }

    /**
     * Locale has a language code and a country code.
     * If the language exists but with another country code, this function
     * returns the locale with the same language - or the default one
     * if the language is not available.
     *
     * @param false|string|null $locale
     * @return string
     */
    private function getAvailableLocale(null|false|string $locale): string
    {
        $availableLocales = $this->localeSettings['available'];

        // If locale is in available locales, return it
        if (in_array($locale, $availableLocales, true)) {
            return $locale;
        }

        // If locale was not found in the available locales, check if the language from another country is available
        $localesMappedByLanguage = [];
        foreach ($availableLocales as $availableLocale) {
            $languageCode = $this->getLanguageCodeFromLocale($availableLocale);
            // If the language code is already in the result array, skip it (the first locale of the
            // language should be default)
            if (!array_key_exists($languageCode, $localesMappedByLanguage)) {
                $localesMappedByLanguage[$languageCode] = $availableLocale;
            }
        }
        // Get the language code from the "target" locale
        $localeLanguageCode = $this->getLanguageCodeFromLocale($locale);
        // Take the locale from the same language if available or the default one
        return $localesMappedByLanguage[$localeLanguageCode] ?? $this->localeSettings['default'] ?? 'en_US';
    }

    /**
     * Get the language code part of a locale.
     *
     * @param string|null $locale e.g. 'en_US'
     * @return string|null e.g. 'en'
     */
    private function getLanguageCodeFromLocale(string|null $locale): ?string
    {
        // If locale has hyphen instead of underscore, replace it
        if ($locale && str_contains($locale, '-')) {
            $locale = str_replace('-', '_', $locale);
        }
        // The language code is the first part of the locale string
        return $locale ? explode('_', $locale)[0] : null;
    }
}
