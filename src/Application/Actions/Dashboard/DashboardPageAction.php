<?php

namespace App\Application\Actions\Dashboard;

use App\Application\Responder\Responder;
use App\Domain\Dashboard\DashboardGetter;
use App\Infrastructure\Client\ClientStatus\ClientStatusFinderRepository;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class DashboardPageAction
{

    /**
     * The constructor.
     * @param Responder $responder
     * @param SessionInterface $session
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly SessionInterface $session,
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
        private readonly DashboardGetter $dashboardGetter,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $name = $args['name'] ?? 'noname';
        $array = [];

        return $this->responder->render(
            $response,
            'dashboard/dashboard.html.php',
            [
                'authenticatedUserId' => $this->session->get('user_id'),
                'dashboards' => $this->dashboardGetter->getDashboards()
            ]
        );
    }
}
