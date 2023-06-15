<?php

namespace App\Application\Middleware;

use App\Application\Responder\Responder;
use App\Domain\User\Service\UserFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UnexpectedValueException;

/**
 * User auth verification middleware.
 *
 * Class UserAuthMiddleware
 */
final class LocaleMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected readonly SessionInterface $session,
        protected readonly Responder $responder,
        protected readonly UserFinder $userFinder,
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $supportedLanguages = [
            'en' => 'en_US',
            'de' => 'de_CH',
            'fr' => 'fr_CH',
        ];

        // Get authenticated user id from session
        $loggedInUserId = $this->session->get('user_id');
        // If there is an authenticated user, find its language from the database
        $userLang = $loggedInUserId ? $this->userFinder->findUserById($loggedInUserId)->language : null;
        // Remove the region from the language code en_US -> en
        $userLangShort = $userLang ? explode('_', $userLang->value)[0] : null;

        // Get browser language. Result is something like: en-GB,en;q=0.9,de;q=0.8,de-DE;q=0.7,en-US;q=0.6,pt;q=0.5,fr;q=0.4
        $browserLang = $request->getHeaderLine('Accept-Language');
        // Get the first (main) language code with region e.g.: en-GB
        $language = explode(',', $browserLang)[0];
        // Retrieve only the language part without region e.g.: en
        $browserLangShort = explode('-', $language)[0];

        // Set the language to the userLang if available and else to the browser language
        $actualLocale = $this->setLanguage($supportedLanguages[$userLangShort ?? $browserLangShort] ?? 'en_US');

        return $handler->handle($request);
    }

    /**
     * Set the locale to the given lang code and bind text domain for gettext translations.
     *
     * @param string $locale The locale (e.g. 'en_US')
     * @param string $domain The text domain (e.g. 'messages')
     *
     * @return false|string
     */
    private function setLanguage(string $locale, string $domain = 'messages'): bool|string
    {
        $codeset = 'UTF-8';
        // Current path src/Application/Middleware
        $directory = __DIR__ . '/../../../resources/translations';
        // Set locale information
        $localeHyphen = str_replace('_', '-', $locale);
        $setLocaleResult = setlocale(LC_ALL, $locale, $localeHyphen);
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

        return $setLocaleResult;
    }
}
