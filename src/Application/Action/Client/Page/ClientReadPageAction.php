<?php

namespace App\Application\Action\Client\Page;

use App\Application\Renderer\TemplateRenderer;
use App\Domain\Client\Service\ClientFinder;
use App\Domain\Client\Service\ClientUtilFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientReadPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private ClientFinder $clientFinder,
        private ClientUtilFinder $clientUtilFinder,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $clientAggregate = $this->clientFinder->findClientReadAggregate((int)$args['client_id']);
        $dropdownValues = $this->clientUtilFinder->findClientDropdownValues($clientAggregate->userId);

        return $this->templateRenderer->render(
            $response,
            'client/client-read.html.php',
            ['clientAggregate' => $clientAggregate, 'dropdownValues' => $dropdownValues]
        );
    }
}
