<?php


namespace App\Application\Middleware;

use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Views\PhpRenderer;

final class PhpViewExtensionMiddleware  implements MiddlewareInterface
{

    protected App $app;
    protected PhpRenderer $phpRenderer;
    protected SessionInterface $session;

    public function __construct(App $app, PhpRenderer $phpRenderer, SessionInterface $session)
    {
        $this->app = $app;
        $this->phpRenderer = $phpRenderer;
        $this->session = $session;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        $this->phpRenderer->addAttribute('uri', $request->getUri());
        $this->phpRenderer->addAttribute('basePath', $this->app->getBasePath());
        $this->phpRenderer->addAttribute('route', $this->app->getRouteCollector()->getRouteParser());

        $this->phpRenderer->addAttribute('flash', $this->session->getFlash());

        return $handler->handle($request);

    }
}
