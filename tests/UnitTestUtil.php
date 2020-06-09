<?php


namespace App\Test;

use DI\Container;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;

trait UnitTestUtil
{
    use AppHandler;

    protected function setUp(): void
    {
        $this->bootApp();
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