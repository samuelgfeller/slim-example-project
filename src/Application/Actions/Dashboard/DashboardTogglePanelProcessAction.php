<?php

namespace App\Application\Actions\Dashboard;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\FilterSetting\FilterModule;
use App\Domain\FilterSetting\FilterSettingSaver;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

final class DashboardTogglePanelProcessAction
{

    public function __construct(
        private readonly Responder $responder,
        private readonly SessionInterface $session,
        private readonly FilterSettingSaver $filterSettingSaver,
        private readonly MalformedRequestBodyChecker $malformedRequestBodyChecker,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @return ResponseInterface The response
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $params = $request->getParsedBody();
        // As there is no other validation the request body is checked for valid keys here
        if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys($params, ['panelIds'])) {
            $this->filterSettingSaver->saveFilterSettingForAuthenticatedUser(
                json_decode($params['panelIds'], true, 512, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR),
                FilterModule::DASHBOARD_PANEL
            );

            return $this->responder->respondWithJson($response, ['success' => true]);
        }
        $flash = $this->session->getFlash();
        $flash->add('error', __('Malformed request body syntax. Please contact an administrator.'));
        // Caught in error handler which displays error page
        throw new HttpBadRequestException($request, 'Request body malformed.');
    }
}
