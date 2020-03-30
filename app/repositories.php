<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Post\PostRepository;
use App\Infrastructure\Persistence\User\UserRepository;
use Cake\Database\Connection;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

/**
 * Needed to give connection to the repositories
 *
 * @param ContainerBuilder $containerBuilder
 */
return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions(
        [
            UserRepository::class => function (ContainerInterface $container) {
                $pdo = $container->get(Connection::class);
                return new UserRepository($pdo);
            },
            PostRepository::class => function (ContainerInterface $container) {
                $pdo = $container->get(Connection::class);
                return new PostRepository($pdo);
            },

        ]
    );
};