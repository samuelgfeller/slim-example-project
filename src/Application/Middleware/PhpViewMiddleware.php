<?php

namespace App\Application\Middleware;

use App\Domain\User\Service\Authorization\UserPermissionVerifier;
use App\Infrastructure\Utility\JsImportCacheBuster;
use App\Infrastructure\Utility\Settings;
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

final class PhpViewMiddleware implements MiddlewareInterface
{
    /** @var array<string, mixed> */
    private array $publicSettings;
    private bool $devSetting;
    /** @var array<string, mixed> */
    private array $deploymentSettings;

    public function __construct(
        private readonly App $app,
        private readonly PhpRenderer $phpRenderer,
        private readonly SessionInterface $session,
        private readonly JsImportCacheBuster $jsImportCacheBuster,
        Settings $settings,
        private readonly UserPermissionVerifier $userPermissionVerifier,
        private readonly RouteParserInterface $routeParser
    ) {
        $this->publicSettings = $settings->get('public');
        $this->devSetting = $settings->get('dev');
        $this->deploymentSettings = $settings->get('deployment');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $loggedInUserId = $this->session->get('user_id');
        // The following has to work even with no connection to mysql to display the error page (layout needs those attr)
        $this->phpRenderer->setAttributes([
            'dev' => $this->devSetting,
            'version' => $this->deploymentSettings['version'],
            'uri' => $request->getUri(),
            'basePath' => $this->app->getBasePath(),
            'route' => $this->routeParser,
            'currRouteName' => RouteContext::fromRequest($request)->getRoute()?->getName(),
            'flash' => $this->session->getFlash(),
            // Used for public values used by view like company email address
            'config' => $this->publicSettings,
            'authenticatedUser' => $loggedInUserId,
        ]);

        // Check and set user list authorization for "users" nav point
        if ($loggedInUserId) {
            // Check if the authenticated user is allowed to see user list and save the result to the session
            $this->checkUserListAuthorization($loggedInUserId);
            // Add the user list authorization as an attribute to the PhpRenderer
            $this->phpRenderer->addAttribute('userListAuthorization', $this->session->get('isAllowedToSeeUserList'));
        }

        // Add version number to js imports
        if ($this->deploymentSettings['update_js_imports_version'] === true) {
            $this->jsImportCacheBuster->addVersionToJsImports();
        }

        return $handler->handle($request);
    }

    /**
     * Check if the user is allowed to see the user list and save the result to the session.
     *
     * @param int $loggedInUserId
     */
    private function checkUserListAuthorization(int $loggedInUserId): void
    {
        // If the session already contains the information, the permission check can be skipped
        if ($this->session->get('isAllowedToSeeUserList') === null) {
            try {
                $isAllowedToSeeUserList = $this->userPermissionVerifier->isGrantedToRead($loggedInUserId + 1, false);
                $this->session->set('isAllowedToSeeUserList', $isAllowedToSeeUserList);
            } catch (DatabaseException $databaseException) {
                // Mysql connection not working. Caught here to prevent error page from crashing
                return;
            }
        }
    }
}
