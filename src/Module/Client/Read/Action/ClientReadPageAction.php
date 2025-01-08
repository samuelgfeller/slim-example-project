<?php

namespace App\Module\Client\Read\Action;

use App\Core\Application\Responder\TemplateRenderer;
use App\Module\Client\Domain\Service\ClientFinder;
use App\Module\Client\DropdownFinder\Service\ClientDropdownFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientReadPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private ClientFinder $clientFinder,
        private ClientDropdownFinder $clientUtilFinder,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $clientReadResult = $this->clientFinder->findClientReadAggregate((int)$args['client_id']);
        $dropdownValues = $this->clientUtilFinder->findClientDropdownValues($clientReadResult->userId);

        return $this->templateRenderer->render(
            $response,
            'client/client-read.html.php',
            ['clientReadData' => $clientReadResult, 'dropdownValues' => $dropdownValues]
        );
    }
}
