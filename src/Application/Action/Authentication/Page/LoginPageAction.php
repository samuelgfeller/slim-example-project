<?php

namespace App\Application\Action\Authentication\Page;

use App\Application\Responder\RedirectHandler;
use App\Application\Responder\TemplateRenderer;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteParserInterface;

final readonly class LoginPageAction
{
    public function __construct(
        private RedirectHandler $redirectHandler,
        private RouteParserInterface $routeParser,
        private TemplateRenderer $templateRenderer,
        private SessionInterface $session,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        // Check if user already logged in
        if ($this->session->get('user_id') !== null) {
            $flash = $this->session->getFlash();
            $flash->add(
                'info',
                sprintf(
                    __('You are already logged-in.<br>Would you like to %slogout%s?'),
                    '<a href="' . $this->routeParser->urlFor('logout') . '">',
                    '</a>'
                )
            );
            // If redirect param set, redirect to this url
            if (isset($queryParams['redirect'])) {
                return $this->redirectHandler->redirectToUrl($response, $queryParams['redirect']);
            }

            // Otherwise, go to home page
            return $this->redirectHandler->redirectToRouteName($response, 'home-page');
        }

        return $this->templateRenderer->render(
            $response,
            'authentication/login.html.php',
            // Provide same query params passed to login page to be added to the login submit request
            ['queryParams' => $request->getQueryParams()]
        );
    }
}
