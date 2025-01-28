<?php

namespace App\Module\Dashboard\TogglePanel\Action;

use App\Application\Responder\JsonResponder;
use App\Module\Dashboard\TogglePanel\Service\ActiveDashboardPanelChanger;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

final readonly class DashboardTogglePanelAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private SessionInterface $session,
        private ActiveDashboardPanelChanger $activeDashboardPanelChanger,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $params = (array)$request->getParsedBody();
        try {
            $this->activeDashboardPanelChanger->toggleDashboardPanel($params);
            return $this->jsonResponder->encodeAndAddToResponse($response, ['success' => true]);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            $flash = $this->session->getFlash();
            $flash->add('error', __('Malformed request body syntax. Please contact an administrator.'));
            // Caught in error handler which displays error page
            throw new HttpBadRequestException($request, 'Request body malformed.');
        }
    }
}
