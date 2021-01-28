<?php


namespace App\Test;

use App\Domain\Factory\LoggerFactory;
use DI\Container;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

trait UnitTestUtil
{
    use AppHandler;

    /**
     * PHP Unit function setUp is called automatically before each test
     */
    protected function setUp(): void
    {
        $this->bootApp();

        // Mock LoggerFactory so that createInstance() returns NullLogger
        // addFileHandler() automatically returns a stub of its return type which is the mock instance itself
        $this->mock(LoggerFactory::class)->method('createInstance')->willReturn(new NullLogger());
    }

    protected function tearDown(): void
    {
        $this->shutdownApp();
    }

    protected function mock(string $class): MockObject
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Class not found: %s', $class));
        }

        $mock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getContainer();
        if ($container instanceof Container) {
            $container->set($class, $mock);
        }

        return $mock;
    }

}