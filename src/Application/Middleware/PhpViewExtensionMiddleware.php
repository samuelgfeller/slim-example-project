<?php


namespace App\Application\Middleware;

use App\Application\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Routing\RouteContext;
use Slim\Views\PhpRenderer;

final class PhpViewExtensionMiddleware  implements MiddlewareInterface
{

    protected App $app;
    protected PhpRenderer $phpRenderer;

    public function __construct(App $app, PhpRenderer $phpRenderer)
    {
        $this->app = $app;
        $this->phpRenderer = $phpRenderer;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        $this->phpRenderer->addAttribute('uri', $request->getUri());
        $this->phpRenderer->addAttribute('basePath', $this->app->getBasePath());
        $this->phpRenderer->addAttribute('route', $this->app->getRouteCollector()->getRouteParser());

        return $handler->handle($request);

    }
}
