<?php

declare(strict_types=1);

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;


return [
    LoggerInterface::class => function () {
        // Return empty instance
        return new Logger('null-logger');
    },
];
