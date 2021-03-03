<?php

declare(strict_types=1);

use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserRepository;
use App\Infrastructure\User\UserVerificationRepository;
use Cake\Database\Connection;
use Psr\Container\ContainerInterface;

/**
 * Giving db connection to repositories here because
 * there can be quickly a lot in bigger projects.
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
    UserVerificationRepository::class => function (ContainerInterface $container) {
        $pdo = $container->get(Connection::class);
        return new UserVerificationRepository($pdo);
    },

];