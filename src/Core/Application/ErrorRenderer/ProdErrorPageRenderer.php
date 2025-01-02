<?php

namespace App\Core\Application\ErrorRenderer;

use Slim\Views\PhpRenderer;
use SlimErrorRenderer\Interfaces\GenericErrorPageRendererInterface;

final readonly class ProdErrorPageRenderer implements GenericErrorPageRendererInterface
{
    public function __construct(
        private PhpRenderer $phpRenderer,
    ) {
    }

    /**
     * Renders the error page for production (without sensitive infos) in html.
     *
     * @param int $statusCode
     * @param string|null $safeExceptionMessage
     * @param string|null $errorReportEmailAddress
     *
     * @throws \Throwable
     *
     * @return string
     */
    public function renderHtmlProdErrorPage(
        int $statusCode,
        ?string $safeExceptionMessage,
        ?string $errorReportEmailAddress,
    ): string {
        return $this->phpRenderer->fetch('error/error-page.html.php', [
            'statusCode' => $statusCode,
            'exceptionMessage' => $safeExceptionMessage,
            'errorReportEmailAddress' => $errorReportEmailAddress,
        ], true);
    }
}
