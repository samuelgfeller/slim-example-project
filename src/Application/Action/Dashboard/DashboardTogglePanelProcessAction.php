<?php

namespace App\Application\Action\Dashboard;

use App\Application\Renderer\JsonEncoder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\FilterSetting\FilterModule;
use App\Domain\FilterSetting\FilterSettingSaver;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

final readonly class DashboardTogglePanelProcessAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private SessionInterface $session,
        private FilterSettingSaver $filterSettingSaver,
        private MalformedRequestBodyChecker $malformedRequestBodyChecker,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $params = (array)$request->getParsedBody();
        // As there is no other validation the request body is checked for valid keys here
        if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys($params, ['panelIds'])) {
            $this->filterSettingSaver->saveFilterSettingForAuthenticatedUser(
                json_decode($params['panelIds'], true, 512, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR),
                FilterModule::DASHBOARD_PANEL
            );

            return $this->jsonEncoder->encodeAndAddToResponse($response, ['success' => true]);
        }
        $flash = $this->session->getFlash();
        $flash->add('error', __('Malformed request body syntax. Please contact an administrator.'));
        // Caught in error handler which displays error page
        throw new HttpBadRequestException($request, 'Request body malformed.');
    }
}
