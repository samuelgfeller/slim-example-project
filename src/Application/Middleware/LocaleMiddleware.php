<?php

namespace App\Application\Middleware;

use App\Application\Responder\Responder;
use App\Common\LocaleHelper;
use App\Domain\Settings;
use App\Domain\User\Service\UserFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * User auth verification middleware.
 *
 * Class UserAuthMiddleware
 */
final class LocaleMiddleware implements MiddlewareInterface
{
    private array $localeSettings;

    public function __construct(
        private readonly SessionInterface $session,
        private readonly Responder $responder,
        private readonly UserFinder $userFinder,
        private readonly LocaleHelper $localeHelper,
        Settings $settings
    ) {
        $this->localeSettings = $settings->get('locale');
    }

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
        $actualLocale = $this->localeHelper->setLanguage(
            $this->localeSettings['available'][$userLangShort ?? $browserLangShort] ?? $this->localeSettings['default']
        );

        return $handler->handle($request);
    }
}
