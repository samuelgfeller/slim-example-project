<?php


namespace App\Application\Middleware;

use App\Domain\Settings;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Views\PhpRenderer;

final class PhpViewExtensionMiddleware implements MiddlewareInterface
{
    private array $publicSettings;

    public function __construct(
        private App $app,
        private PhpRenderer $phpRenderer,
        private SessionInterface $session,
        Settings $settings
    ) {
        $this->publicSettings = $settings->get('public');
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $this->phpRenderer->addAttribute('uri', $request->getUri());
        $this->phpRenderer->addAttribute('basePath', $this->app->getBasePath());
        $this->phpRenderer->addAttribute('route', $this->app->getRouteCollector()->getRouteParser());

        $this->phpRenderer->addAttribute('flash', $this->session->getFlash());
        // Used for public values used by view like company email address
        $this->phpRenderer->addAttribute('config', $this->publicSettings);

        return $handler->handle($request);
    }
}
