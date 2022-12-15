<?php

namespace App\Test\Traits;

/**
 * Slim App Route Test Trait.
 */
trait RouteTestTrait
{
    /**
     * Build the path for a named route including the base path.
     *
     * @param string $routeName Route name
     * @param string[] $data Named argument replacement data
     * @param string[] $queryParams Optional query string parameters. If you're using Nyholm/psr7 query parameters
     * MUST be added via $request->withQueryParams($queryParams) to be retrieved with $request->getQueryParams();
     *
     * @return string route with base path
     */
    protected function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->app->getRouteCollector()->getRouteParser()->urlFor($routeName, $data, $queryParams);
    }
}
