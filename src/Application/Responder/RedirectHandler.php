<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;

final readonly class RedirectHandler
{
    public function __construct(private RouteParserInterface $routeParser)
    {
    }

    /**
     * Creates a redirect for the given url.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param string $destination The redirect destination (url or route name)
     * @param array<string, int|string> $queryParams Optional query string parameters
     *
     * @return ResponseInterface The response
     */
    public function redirectToUrl(
        ResponseInterface $response,
        string $destination,
        array $queryParams = [],
    ): ResponseInterface {
        if ($queryParams) {
            $destination = sprintf('%s?%s', $destination, http_build_query($queryParams));
        }

        return $response->withStatus(302)->withHeader('Location', $destination);
    }

    /**
     * Creates a redirect for the given route name.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param string $routeName The redirect route name
     * @param array $data Named argument replacement data
     * @param array<string, string> $queryParams Optional query string parameters
     *
     * @return ResponseInterface The response
     */
    public function redirectToRouteName(
        ResponseInterface $response,
        string $routeName,
        array $data = [],
        array $queryParams = [],
    ): ResponseInterface {
        return $this->redirectToUrl($response, $this->routeParser->urlFor($routeName, $data, $queryParams));
    }
}
