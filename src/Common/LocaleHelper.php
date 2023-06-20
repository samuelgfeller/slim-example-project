<?php

namespace App\Common;

use App\Domain\Settings;
use App\Domain\User\Enum\UserLang;

final class LocaleHelper
{
    private array $availableLocales;

    public function __construct(Settings $settings)
    {
        $this->availableLocales = $settings->get('locale')['available'];
    }

    /**
     * Returns the current language code of the set locale with an
     * added slash "/" at the end of the string if not empty.
     *
     * @param UserLang|null $preferredLang
     * @return string language code or empty string if default or language code not found
     */
    public function getLanguageCodeForPath(?UserLang $preferredLang = null): string
    {
        // Get the key of the current locale or use the preferred given UserLang
        $localeCode = $preferredLang?->value ?? setlocale(LC_ALL, 0);
        // Available locales keys are language codes ('en', 'de') and values are locale codes ('en_US', 'de_CH')
        $langCode = array_search($localeCode, $this->availableLocales, true) ?: '';
        // If language code is 'en' return empty string as default email templates are in english and not in a sub folder
        // If language code is not empty, add a slash to complete the path it will be inserted into
        return $langCode === 'en' || $langCode === '' ? '' : $langCode . '/';
    }
}
