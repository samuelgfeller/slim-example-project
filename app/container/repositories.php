<?php

declare(strict_types=1);

use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserRepository;
use Cake\Database\Connection;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

/**
 * Needed to give connection to the repositories
 *
 * @param ContainerBuilder $containerBuilder
 */
return [
    UserRepository::class => function (ContainerInterface $container) {
        $pdo = $container->get(Connection::class);
        return new UserRepository($pdo);
    },
    PostRepository::class => function (ContainerInterface $container) {
        $pdo = $container->get(Connection::class);
        return new PostRepository($pdo);
    },

];