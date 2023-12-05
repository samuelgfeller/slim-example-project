<?php

namespace App\Application\Middleware;

use App\Common\LocaleHelper;
use App\Domain\User\Service\UserFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class LocaleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SessionInterface $session,
        private UserFinder $userFinder,
        private LocaleHelper $localeHelper,
    ) {
    }

    /**
     * Locale middleware set language to default lang, browser lang or from settings.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
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
        $actualLocale = $this->localeHelper->setLanguage($userLangShort ?? $browserLangShort);

        return $handler->handle($request);
    }
}
