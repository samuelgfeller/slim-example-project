<?php

namespace App\Application\Action\Authentication\Page;

use App\Application\Renderer\TemplateRenderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;

readonly class PasswordResetPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Check if the token is valid and if yes display password form.
     *
     * @param ServerRequest $request
     * @param Response $response
     *
     * @throws \Throwable
     *
     * @return Response
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();

        // There may be other query params, e.g. redirect
        if (isset($queryParams['id'], $queryParams['token'])) {
            return $this->templateRenderer->render($response, 'authentication/reset-password.html.php', [
                'token' => $queryParams['token'],
                'id' => $queryParams['id'],
            ]);
        }

        $this->logger->error(
            'GET request malformed: ' . json_encode($queryParams, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR)
        );
        // If the user clicks on the link and the token's missing, load page with 400 Bad Request
        $response = $response->withStatus(400);

        return $this->templateRenderer->render($response, 'authentication/reset-password.html.php', [
            'formErrorMessage' => __('Token not found. Please click on the link you received via email.'),
        ]);
    }
}
