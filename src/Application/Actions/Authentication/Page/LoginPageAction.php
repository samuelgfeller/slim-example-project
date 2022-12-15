<?php

namespace App\Application\Actions\Authentication\Page;

use App\Application\Responder\Responder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class LoginPageAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param SessionInterface $session
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly SessionInterface $session,
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
                'You are already logged-in.<br>Would you like to <a href="' . $this->responder->urlFor('logout') .
                '">logout</a>?'
            );
            // If redirect param set, redirect to this url
            if (isset($queryParams['redirect'])) {
                return $this->responder->redirectToUrl($response, $queryParams['redirect']);
            }
            // Otherwise, go to home page
            return $this->responder->redirectToRouteName($response, 'home-page');
        }

        return $this->responder->render(
            $response,
            'authentication/login.html.php',
            // Provide same query params passed to login page to be added to the login submit request
            ['queryParams' => $request->getQueryParams()]
        );
    }
}
