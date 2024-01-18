<?php

namespace App\Application\Renderer;

use Psr\Http\Message\ResponseInterface;

class JsonEncoder
{
    /**
     * Write JSON to the response body.
     *
     * This method prepares the response object to return an HTTP JSON
     * response to the client.
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
