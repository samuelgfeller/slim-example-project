<?php


namespace App\Test;


use DI\Container;
use Psr\Container\ContainerInterface;
use Slim\App;
use UnexpectedValueException;

/**
 * Handles slim app for testing
 */
trait AppHandler
{
    protected ?ContainerInterface $container;

    protected ?App $app;

    /**
     * Bootstrap app.
     *
     * @return void
     */
    protected function bootApp(): void
    {
        $this->app = require __DIR__ . '/bootstrap.php';
        $this->container = $this->app->getContainer();
    }

    /**
     * Shutdown app.
     *
     * @return void
     */
    protected function shutdownApp(): void
    {
        $this->app = null;
        $this->container = null;
    }

    /**
     * Get container.
     *
     * @throws UnexpectedValueException
     *
     * @return ContainerInterface|Container The container
     */
    protected function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            throw new UnexpectedValueException('Container must be initialized');
        }

        return $this->container;
    }

    /**
     * Get app.
     *
     * @throws UnexpectedValueException
     *
     * @return App The app
     */
    protected function getApp(): App
    {
        if ($this->app === null) {
            throw new UnexpectedValueException('App must be initialized');
        }

        return $this->app;
    }
}