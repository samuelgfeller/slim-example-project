<?php

namespace App\Test\Traits;

use App\Domain\Factory\LoggerFactory;
use Odan\Session\MemorySession;
use Odan\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\MockTestTrait;
use Slim\App;
use UnexpectedValueException;

/**
 * Handles slim app for testing
 * Traits basically "extend" the class that include them with their content.
 * Or simply "language assisted copy and paste" (from PHP docs comments)
 */
trait AppTestTrait
{

    use HttpTestTrait;
    use MockTestTrait;

    protected ContainerInterface $container;

    protected App $app;

    /**
     * PHP Unit function setUp is called automatically before each test
     */
    protected function setUp(): void
    {
        // Start slim app
        $this->app = require __DIR__ . '/../../config/bootstrap.php';

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
            $this->setUpDatabase(__DIR__ . '/../../resources/schema/schema.sql');
        }

        // Per default not set when script executed with cli and used at least in all security checks
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // XDebug start_with_request produces errors when testing (SLE-102)
        if (!isset($_ENV['AUTO_XDEBUG_DISABLED'])){
            // Disable xdebug.start_with_request (when already disabled, delay is approx 200ms for 80 tests)
            shell_exec(__DIR__ . '/../../resources/scripts/1_disable_autostart_minimized_shortcut.lnk');
            $_ENV['AUTO_XDEBUG_DISABLED'] = true;
//            self::fail('XDebug start_with_request was enabled. It is now disabled, please run the test again');
        }
    }
}