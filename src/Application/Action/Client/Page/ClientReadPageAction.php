<?php

namespace App\Application\Action\Client\Page;

use App\Application\Responder\TemplateRenderer;
use App\Domain\Client\Service\ClientFinder;
use App\Domain\Client\Service\ClientUtilFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ClientReadPageAction
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly ClientFinder $clientFinder,
        private readonly ClientUtilFinder $clientUtilFinder,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @throws \JsonException|\Throwable
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $clientAggregate = $this->clientFinder->findClientReadAggregate((int)$args['client_id'], false);
        $dropdownValues = $this->clientUtilFinder->findClientDropdownValues($clientAggregate->userId);

        return $this->templateRenderer->render(
            $response,
            'client/client-read.html.php',
            ['clientAggregate' => $clientAggregate, 'dropdownValues' => $dropdownValues]
        );
    }
}