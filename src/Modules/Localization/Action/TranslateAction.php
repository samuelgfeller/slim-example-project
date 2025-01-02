<?php

namespace App\Modules\Localization\Action;

use App\Core\Application\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TranslateAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
    ) {
    }

    /**
     * Returns strings provided in requests as translated strings.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $translatedStrings = [];
        if (isset($queryParams['strings']) && is_array($queryParams['strings'])) {
            foreach ($queryParams['strings'] as $string) {
                $translatedStrings[$string] = __($string);
            }

            return $this->jsonResponder->encodeAndAddToResponse($response, $translatedStrings);
        }

        return $this->jsonResponder->encodeAndAddToResponse($response, ['error' => 'Wrong request body format.'], 400);
    }
}
