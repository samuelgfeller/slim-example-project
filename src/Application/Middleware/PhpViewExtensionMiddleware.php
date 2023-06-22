<?php

namespace App\Application\Middleware;

use App\Common\JsImportVersionAdder;
use App\Domain\Settings;
use App\Domain\User\Authorization\UserAuthorizationChecker;
use Cake\Database\Exception\DatabaseException;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Interfaces\RouteParserInterface;
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
        Settings $settings,
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
        private readonly RouteParserInterface $routeParser
    ) {
        $this->publicSettings = $settings->get('public');
        $this->devSetting = $settings->get('dev');
        $this->appVersion = $settings->get('deployment')['version'];
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $loggedInUserId = $this->session->get('user_id');
        // The following has to work even with no connection to mysql to display the error page (layout needs those attr)
        $this->phpRenderer->setAttributes([
            'title' => 'Slim Example Project',
            'dev' => $this->devSetting,
            'version' => $this->appVersion,
            'uri' => $request->getUri(),
            'basePath' => $this->app->getBasePath(),
            'route' => $this->routeParser,
            'currRouteName' => RouteContext::fromRequest($request)->getRoute()?->getName(),
            'flash' => $this->session->getFlash(),
            // Used for public values used by view like company email address
            'config' => $this->publicSettings,
            'authenticatedUser' => $loggedInUserId,
        ]);
        // Check if granted to read user that is different then the authenticated user itself (+1)
        // this determines if the nav point "users" is visible in the layout
        if ($loggedInUserId) {
            try {
                $this->phpRenderer->addAttribute(
                    'userListAuthorization',
                    $this->userAuthorizationChecker->isGrantedToRead($loggedInUserId + 1, false)
                );
            } catch (DatabaseException $databaseException) {
                // Mysql connection not working
            }
        }

        // Add version number to js imports
        $this->jsImportVersionAdder->addVersionToJsImports();

        return $handler->handle($request);
    }
}
