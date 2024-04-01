<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseInterface;

final readonly class JsonResponder
{
    /**
     * Write JSON to the response body.
     *
     * @param ResponseInterface $response The response
     * @param mixed $data The data
     * @param int $status
     *
     * @return ResponseInterface The response
     */
    public function encodeAndAddToResponse(
        ResponseInterface $response,
        mixed $data = null,
        int $status = 200
    ): ResponseInterface {
        $response->getBody()->write((string)json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR));
        $response = $response->withStatus($status);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
