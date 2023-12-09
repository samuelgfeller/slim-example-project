<?php

namespace App\Application\Middleware;

use App\Domain\User\Service\UserFinder;
use App\Infrastructure\Service\LocaleConfigurator;
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
        private LocaleConfigurator $localeConfigurator,
    ) {
    }

    /**
     * Sets language to the user's choice in the database or browser language.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get authenticated user id from session
        $loggedInUserId = $this->session->get('user_id');
        // If there is an authenticated user, find their language in the database
        $locale = $loggedInUserId ? $this->userFinder->findUserById($loggedInUserId)->language : null;
        // Get browser language if no user language is set
        if (!$locale) {
            // Result is something like: en-GB,en;q=0.9,de;q=0.8,de-DE;q=0.7,en-US;q=0.6,pt;q=0.5,fr;q=0.4
            $browserLang = $request->getHeaderLine('Accept-Language');
            // Get the first (main) language code with region e.g.: en-GB
            $locale = explode(',', $browserLang)[0];
        }
        // Set the language to the userLang if available and else to the browser language
        $this->localeConfigurator->setLanguage($locale);

        return $handler->handle($request);
    }
}
