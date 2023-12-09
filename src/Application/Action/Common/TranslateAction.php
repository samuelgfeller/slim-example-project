<?php

namespace App\Application\Action\Common;

use App\Application\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TranslateAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $translatedStrings = [];
        if (isset($queryParams['strings']) && is_array($queryParams['strings'])) {
            foreach ($queryParams['strings'] as $string) {
                $translatedStrings[$string] = __($string);
            }

            return $this->jsonResponder->respondWithJson($response, $translatedStrings);
        }

        return $this->jsonResponder->respondWithJson($response, ['error' => 'Wrong request body format.'], 400);
    }
}
