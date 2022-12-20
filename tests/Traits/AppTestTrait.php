<?php

namespace App\Test\Traits;

use App\Domain\Factory\LoggerFactory;
use App\Test\Fixture\UserRoleFixture;
use Cake\Database\Connection;
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
 * Or simply "language assisted copy and paste" (from PHP docs comments).
 */
trait AppTestTrait
{
    use HttpTestTrait;
    use MockTestTrait;

    protected ContainerInterface $container;

    protected App $app;

    /**
     * PHP Unit function setUp is called automatically before each test.
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
            // Check that database name in config contains the word "test"
            // This is a double security check to prevent unwanted use of dev db for testing
            if (!str_contains($container->get('settings')['db']['database'], 'test')) {
                throw new UnexpectedValueException('Test database name MUST contain the word "test"');
            }

            // Create tables, truncate old ones
            $this->setUpDatabase($container->get('settings')['root'] . '/resources/schema/schema.sql');
            // Always insert user roles so that it doesn't have to be done inside each test function that uses users
            $this->insertFixtures([UserRoleFixture::class]);
        }

        // Per default not set when script executed with cli and used at least in all security checks
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // XDebug start_with_request produces errors when testing (SLE-102)
        // if (!isset($_ENV['AUTO_XDEBUG_DISABLED'])) {
        // Disable xdebug.start_with_request (when already disabled, delay is approx 200ms for 80 tests)
        // shell_exec(__DIR__ . '/../../resources/scripts/1_disable_autostart_minimized_shortcut.lnk');
        // $_ENV['AUTO_XDEBUG_DISABLED'] = true;
//            self::fail('XDebug start_with_request was enabled. It is now disabled, please run the test again');
//         }
    }

    /**
     * Function called after each test
     * Close database connection to prevent errors:
     *  - PDOException: Packets out of order. Expected 0 received 1. Packet size=23
     *  - PDOException: SQLSTATE[HY000] [2006] MySQL server has gone away
     *  - Cake\Database\Exception\MissingConnectionException:
     *        Connection to Mysql could not be established: SQLSTATE[08004] [1040] Too many connections.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return void
     */
    protected function tearDown(): void
    {
        if (method_exists($this, 'setUpDatabase')) {
            $connection = $this->container->get(Connection::class);
            $connection->disconnect();
            $container = $this->container->get(App::class)->getContainer();
            $container->set(Connection::class, null);
        }
    }
}
