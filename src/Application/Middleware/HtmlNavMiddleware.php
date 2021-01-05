<?php


namespace App\Application\Middleware;

use App\Application\Responder\UrlGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\PhpRenderer;

/**
 * Middleware which defines the menu that should be created in the layout
 * Since it uses the route names, routing has to be done before. This is a
 * Middleware that is active when request is exiting so it has to be before
 * the routing middleware because of Last In First Out
 */
final class HtmlNavMiddleware implements MiddlewareInterface
{

    private PhpRenderer $phpRenderer;

    /**
     * @param PhpRenderer $phpRenderer
     */
    public function __construct(PhpRenderer $phpRenderer)
    {
        $this->phpRenderer = $phpRenderer;
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // If injected via constructor (autowire) ServerRequestInterface is not set
        $urlGenerator = new UrlGenerator($request);

        // get route name
        $rn = $request->getAttribute('__route__')->getName();

        // Defining routes without colors
        $routes = [
            'Home' => ['link' => $urlGenerator->urlFor('hello'), 'active' => $rn === 'hello'],
            'Users' => ['link' => $urlGenerator->urlFor('user-list'), 'active' => $rn === 'user-list'],
            'Profile' => ['link' => $urlGenerator->urlFor('profile'), 'active' => $rn === 'profile'],
            'Own posts' => ['link' => $urlGenerator->urlFor('post-list-own'),
                'active' => $rn === 'post-list-own'],
            'All posts' => ['link' => $urlGenerator->urlFor('post-list-all'),
                'active' => $rn === 'post-list-all'],
            'Login' => ['link' => $urlGenerator->urlFor('login-page'), 'active' => $rn === 'login-page'],
            'Register' => ['link' => $urlGenerator->urlFor('register-page'),
                'active' => $rn === 'register-page'],
        ];
        $routes = [ // temp
            'Home' => ['link' => $urlGenerator->urlFor('hello'), 'active' => $rn === 'hello'],
            'Users' => ['link' => $urlGenerator->urlFor('hello'), 'active' => $rn === 'user-list'],
            'Profile' => ['link' => $urlGenerator->urlFor('hello'), 'active' => $rn === 'profile'],
            'Own posts' => ['link' => $urlGenerator->urlFor('hello'),
                'active' => $rn === 'post-list-own'],
            'All posts' => ['link' => $urlGenerator->urlFor('hello'),
                'active' => $rn === 'post-list-all'],
            'Login' => ['link' => $urlGenerator->urlFor('hello'), 'active' => $rn === 'login-page'],
            'Register' => ['link' => $urlGenerator->urlFor('hello'),
                'active' => $rn === 'register-page'],
        ];

        // chocolate
        $colors = ['darkred', 'red', 'firebrick', 'darkorange', 'orange', 'gold',
            'yellow', 'springgreen', 'limegreen', 'green', 'darkcyan', 'teal','royalblue', 'mediumblue',
            'slateblue', 'rebeccapurple', 'indigo', 'orchid', 'violet', 'palevioletred'];

        $interval = floor(count($colors) / count($routes));

        // Populate with colors
        $routesWithColors = [];
        $x = 0; // Used, to add to interval
        foreach ($routes as $name => $route){
            $route['color'] = $colors[$interval + $x];
            $routesWithColors[$name] = $route;
            $x += $interval;
        }

        $this->phpRenderer->addAttribute('routes', $routesWithColors);

        return $handler->handle($request);
    }
}
