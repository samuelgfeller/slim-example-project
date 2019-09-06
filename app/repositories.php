<?php
declare(strict_types=1);

use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Persistence\User\UserRepository;
use Cake\Database\Connection;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        UserRepositoryInterface::class => function (ContainerInterface $container) {
            $pdo = $container->get(Connection::class);
            return new UserRepository($pdo);
        },
    ]);
};
