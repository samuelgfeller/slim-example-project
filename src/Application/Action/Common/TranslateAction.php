<?php

namespace App\Application\Action\Common;

use App\Application\Responder\JsonEncoder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TranslateAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $translatedStrings = [];
        if (isset($queryParams['strings']) && is_array($queryParams['strings'])) {
            foreach ($queryParams['strings'] as $string) {
                $translatedStrings[$string] = __($string);
            }

            return $this->jsonEncoder->encodeAndAddToResponse($response, $translatedStrings);
        }

        return $this->jsonEncoder->encodeAndAddToResponse($response, ['error' => 'Wrong request body format.'], 400);
    }
}
