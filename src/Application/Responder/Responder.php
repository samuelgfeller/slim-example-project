<?php

namespace App\Application\Responder;

use App\Domain\Security\Exception\SecurityException;
use App\Domain\Validation\ValidationException;
use App\Domain\Validation\ValidationResult;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\PhpRenderer;

use function http_build_query;

/**
 * A generic responder.
 */
final class Responder
{
    /**
     * The constructor.
     *
     * @param RouteParserInterface $routeParser The route parser
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param PhpRenderer $phpRenderer slimphp/PHP-View renderer
     */
    public function __construct(
        private readonly RouteParserInterface $routeParser,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly PhpRenderer $phpRenderer
    ) {
    }

    /**
     * Create a new response.
     *
     * @return ResponseInterface The response
     */
    public function createResponse(): ResponseInterface
    {
        return $this->responseFactory->createResponse()->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Output rendered template.
     *
     * @param ResponseInterface $response The response
     * @param string $template Template pathname relative to templates directory
     * @param array $data Associative array of template variables
     *
     * @throws \Throwable
     *
     * @return ResponseInterface The response
     */
    public function render(
        ResponseInterface $response,
        string $template,
        array $data = []
    ): ResponseInterface {
        return $this->phpRenderer->render($response, $template, $data);
    }

    /**
     * Add global variable accessible in templates.
     *
     * @param string $key
     * @param $value
     *
     * @return void
     */
    public function addPhpViewAttribute(string $key, $value): void
    {
        $this->phpRenderer->addAttribute($key, $value);
    }

    /**
     * Creates a redirect for the given url.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param string $destination The redirect destination (url or route name)
     * @param array<mixed> $queryParams Optional query string parameters
     *
     * @return ResponseInterface The response
     */
    public function redirectToUrl(
        ResponseInterface $response,
        string $destination,
        array $queryParams = []
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
     * @param array<mixed> $data Named argument replacement data
     * @param array<mixed> $queryParams Optional query string parameters
     *
     * @return ResponseInterface The response
     */
    public function redirectToRouteName(
        ResponseInterface $response,
        string $routeName,
        array $data = [],
        array $queryParams = []
    ): ResponseInterface {
        return $this->redirectToUrl($response, $this->routeParser->urlFor($routeName, $data, $queryParams));
    }

    /**
     * Build the path for a named route including the base path.
     *
     * @param string $routeName Route name
     * @param string[] $data Named argument replacement data
     * @param string[] $queryParams Optional query string parameters
     *
     * @return string
     */
    public function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }

    /**
     * Render template with validation errors.
     *
     * @param ResponseInterface $response
     * @param string $template
     * @param ValidationException $validationException
     * @param array $queryParams same query params passed to page to be added again to form after validation error
     * @param array|null $preloadValues
     *
     * @throws \Throwable
     *
     * @return ResponseInterface|null
     */
    public function renderOnValidationError(
        ResponseInterface $response,
        string $template,
        ValidationException $validationException,
        array $queryParams = [],
        array $preloadValues = null,
    ): ?ResponseInterface {
        // $this->phpRenderer->addAttribute('formErrorMessage', $validationException->getMessage());
        $this->phpRenderer->addAttribute('preloadValues', $preloadValues);

        // Add the validation errors to phpRender attributes
        $validationResult = $validationException->getValidationResult();
        $this->phpRenderer->addAttribute('validation', $validationResult->toArray());
        $this->phpRenderer->addAttribute('formError', true);
        // Provide same query params passed to page to be added again after validation error (e.g. redirect)
        $this->phpRenderer->addAttribute('queryParams', $queryParams);

        // Render template with status code
        return $this->render($response->withStatus($validationResult->getStatusCode()), $template);
    }

    /**
     * Respond with delay user has to wait or action that needs to be made before repeating the action.
     *
     * @param ResponseInterface $response
     * @param int|string $remainingDelay
     * @param string $template
     * @param array|null $preloadValues
     * @param array $queryParams same query params passed to page to be added again to form after validation error
     *
     * @throws \Throwable
     *
     * @return ResponseInterface
     */
    public function respondWithThrottle(
        ResponseInterface $response,
        int|string $remainingDelay,
        string $template,
        array $preloadValues = null,
        array $queryParams = []
    ): ResponseInterface {
        $this->phpRenderer->addAttribute('throttleDelay', $remainingDelay);
        $this->phpRenderer->addAttribute('preloadValues', $preloadValues);
        $this->phpRenderer->addAttribute('formError', true);
        // Provide same query params passed to page to be added again after validation error (e.g. redirect)
        $this->phpRenderer->addAttribute('queryParams', $queryParams);

        return $this->render($response->withStatus(422), $template);
    }

    /**
     * Respond with delay user has to wait or action that needs to be made before repeating the action.
     * Specifically for form errors.
     *
     * @param ResponseInterface $response
     * @param string $template
     * @param SecurityException $securityException
     * @param array|null $preloadValues
     * @param array $queryParams same query params passed to page to be added again to form after validation error
     *
     * @throws \Throwable
     *
     * @return ResponseInterface
     */
    public function respondWithFormThrottle(
        ResponseInterface $response,
        string $template,
        SecurityException $securityException,
        array $queryParams = [],
        array $preloadValues = null,
    ): ResponseInterface {
        $this->phpRenderer->addAttribute('throttleDelay', $securityException->getRemainingDelay());
        $this->phpRenderer->addAttribute('formErrorMessage', $securityException->getPublicMessage());
        $this->phpRenderer->addAttribute('preloadValues', $preloadValues);
        $this->phpRenderer->addAttribute('formError', true);

        // Provide same query params passed to page to be added again after validation error (e.g. redirect)
        $this->phpRenderer->addAttribute('queryParams', $queryParams);

        return $this->render($response->withStatus(422), $template);
    }

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
    public function respondWithJson(
        ResponseInterface $response,
        $data = null,
        int $status = 200
    ): ResponseInterface {
        $response->getBody()->write((string)json_encode($data, JSON_THROW_ON_ERROR));
        $response = $response->withStatus($status);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function respondWithJsonOnValidationError(
        ValidationResult $validationResult,
        ResponseInterface $response
    ): ?ResponseInterface {
        $responseData = [
            'status' => 'error',
            'message' => 'Validation error',
            'data' => $validationResult->toArray(),
        ];

        return $this->respondWithJson($response, $responseData, $validationResult->getStatusCode());
    }
}
