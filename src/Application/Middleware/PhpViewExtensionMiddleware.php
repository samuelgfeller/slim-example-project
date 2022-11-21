<?php


namespace App\Application\Middleware;

use App\Common\JsImportVersionAdder;
use App\Domain\Settings;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Routing\RouteContext;
use Slim\Views\PhpRenderer;

final class PhpViewExtensionMiddleware implements MiddlewareInterface
{
    private array $publicSettings;
    private bool $devSetting;
    private string $appVersion;

    public function __construct(
        private readonly App $app,
        private readonly PhpRenderer $phpRenderer,
        private readonly SessionInterface $session,
        private readonly JsImportVersionAdder $jsImportVersionAdder,
        Settings $settings
    ) {
        $this->publicSettings = $settings->get('public');
        $this->devSetting = $settings->get('dev');
        $this->appVersion = $settings->get('version');
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $this->phpRenderer->setAttributes([
            'title' => 'Slim Example Project',
            'dev' => $this->devSetting,
            'version' => $this->appVersion,
            'uri' => $request->getUri(),
            'basePath' => $this->app->getBasePath(),
            'route' => $this->app->getRouteCollector()->getRouteParser(),
            'currRouteName' => (RouteContext::fromRequest($request)->getRoute())->getName(),
            'flash' => $this->session->getFlash(),
            // Used for public values used by view like company email address
            'config' => $this->publicSettings,
        ]);

        // Add version number to js imports
        $this->jsImportVersionAdder->addVersionToJsImports();

        return $handler->handle($request);
    }
}
