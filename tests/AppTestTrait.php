<?php

namespace App\Test;

use App\Domain\Factory\LoggerFactory;
use Odan\Session\MemorySession;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;
use Selective\TestTrait\Traits\HttpTestTrait;
use Slim\App;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Handles slim app for testing
 * Traits basically "extend" the class that include them with their content.
 * Or simply "language assisted copy and paste" (from PHP docs comments)
 */
trait AppTestTrait
{

    use HttpTestTrait;

    protected ContainerInterface $container;

    protected App $app;

    /**
     * PHP Unit function setUp is called automatically before each test
     */
    protected function setUp(): void
    {
        // Start slim app
        $this->app = require __DIR__ . '/../app/bootstrap.php';

        // Set $this->container to container instance
        $container = $this->app->getContainer();
        if ($container === null) {
            throw new UnexpectedValueException('Container must be initialized');
        }
        $this->container = $container;

        // Set memory sessions
        $this->container->set(SessionInterface::class, new MemorySession());

        // Mock LoggerFactory so that createInstance() returns NullLogger
        // addFileHandler() automatically returns a stub of its return type which is the mock instance itself
        $this->mock(LoggerFactory::class)->method('createInstance')->willReturn(new NullLogger());

        // If setUp() is called in a testClass that uses DatabaseTestTrait, the method setUpDatabase() exists
        if (method_exists($this, 'setUpDatabase')) {
            // Check that database name in config contains the the word "test"
            // This is a double security check to prevent unwanted use of dev db for testing
            if (strpos($container->get('settings')['db']['database'], 'test') === false) {
                throw new UnexpectedValueException('Test database name MUST contain the word "test"');
            }

            // Create tables, truncate old ones
            $this->setUpDatabase(__DIR__ . '/../resources/schema/schema.sql');
        }

        // Per default not set when script executed with cli and used in all security checks
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
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
}