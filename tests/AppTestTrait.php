<?php


namespace App\Test;


use App\Domain\Factory\LoggerFactory;
use DI\Container;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\NullLogger;
use Slim\App;
use InvalidArgumentException;
use Slim\Psr7\Factory\ServerRequestFactory;
use UnexpectedValueException;

/**
 * Handles slim app for testing
 * Traits basically "extend" the class that include them with their content.
 * Or simply "language assisted copy and paste" (from PHP docs comments)
 */
trait AppTestTrait
{

    protected ContainerInterface $container;

    protected App $app;

    /**
     * PHP Unit function setUp is called automatically before each test
     */
    protected function setUp(): void
    {
        $this->app = require __DIR__ . '/bootstrap.php';

        $container = $this->app->getContainer();
        if ($container === null) {
            throw new UnexpectedValueException('Container must be initialized');
        }
        $this->container = $container;

        // Mock LoggerFactory so that createInstance() returns NullLogger
        // addFileHandler() automatically returns a stub of its return type which is the mock instance itself
        $this->mock(LoggerFactory::class)->method('createInstance')->willReturn(new NullLogger());
    }

    protected function mock(string $class): MockObject
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Class not found: %s', $class));
        }

        $mock = $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();

        $this->container->set($class, $mock);

        return $mock;
    }

    /**
     * Create a server request.
     *
     * @param string $method The HTTP method
     * @param string|UriInterface $uri The URI
     * @param array $serverParams The server parameters
     *
     * @return ServerRequestInterface
     */
    protected function createRequest(
        string $method,
        $uri,
        array $serverParams = []
    ): ServerRequestInterface {
        return (new ServerRequestFactory())->createServerRequest($method, $uri, $serverParams);
    }

    /**
     * Create a JSON request.
     *
     * @param string $method The HTTP method
     * @param string|UriInterface $uri The URI
     * @param array|null $data The json data
     *
     * @return ServerRequestInterface
     */
    protected function createJsonRequest(
        string $method,
        $uri,
        array $data = null
    ): ServerRequestInterface {
        $request = $this->createRequest($method, $uri);

        if ($data !== null) {
            $request = $request->withParsedBody($data);
        }

        return $request->withHeader('Content-Type', 'application/json');
    }

    /**
     * Verify that the given array is an exact match for the JSON returned.
     *
     * @param array $expected The expected array
     * @param ResponseInterface $response The response
     *
     * @return void
     * @throws \JsonException
     */
    protected function assertJsonData(array $expected, ResponseInterface $response): void
    {
        $actual = (string)$response->getBody();
        self::assertSame($expected, (array)json_decode($actual, true, 512, JSON_THROW_ON_ERROR));
    }
}