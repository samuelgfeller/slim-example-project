<?php

namespace App\Application\Action\Authentication\Page;

use App\Application\Responder\TemplateRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class PasswordResetPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Check if the token is valid and if yes display password form.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @throws \Throwable
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        // There may be other query params, e.g. redirect
        if (isset($queryParams['id'], $queryParams['token'])) {
            return $this->templateRenderer->render($response, 'authentication/reset-password.html.php', [
                'token' => $queryParams['token'],
                'id' => $queryParams['id'],
            ]);
        }
        // Replace token from query params with ***
        $queryParams['token'] = '***';
        // Log error
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
