<?php

namespace App\Module\Dashboard\ToggleView;

use App\Core\Application\Responder\JsonResponder;
use App\Core\Application\Validation\RequestBodyKeyValidator;
use App\Module\FilterSetting\Enum\FilterModule;
use App\Module\FilterSetting\Save\Service\FilterSettingSaver;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

final readonly class DashboardTogglePanelProcessAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private SessionInterface $session,
        private FilterSettingSaver $filterSettingSaver,
        private RequestBodyKeyValidator $requestBodyKeyValidator,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $params = (array)$request->getParsedBody();
        // As there is no other validation, the request body is checked for valid keys here
        if ($this->requestBodyKeyValidator->requestBodyHasValidKeys($params, ['panelIds'])) {
            $this->filterSettingSaver->saveFilterSettingForAuthenticatedUser(
                json_decode($params['panelIds'], true, 512, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR),
                FilterModule::DASHBOARD_PANEL
            );

            return $this->jsonResponder->encodeAndAddToResponse($response, ['success' => true]);
        }
        $flash = $this->session->getFlash();
        $flash->add('error', __('Malformed request body syntax. Please contact an administrator.'));
        // Caught in error handler which displays error page
        throw new HttpBadRequestException($request, 'Request body malformed.');
    }
}
