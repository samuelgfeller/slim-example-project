<?php

namespace App\Application\Actions\Common;

use App\Application\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TranslateAction
{
    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $queryParams = $request->getQueryParams();
        $translatedStrings = [];
        if (isset($queryParams['strings']) && is_array($queryParams['strings'])) {
            foreach ($queryParams['strings'] as $string) {
                $translatedStrings[$string] = __($string);
            }

            return $this->responder->respondWithJson($response, $translatedStrings);
        }

        return $this->responder->respondWithJson($response, ['error' => 'Wrong request body format.'], 400);
    }
}
