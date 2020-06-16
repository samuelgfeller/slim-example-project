<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


return [
    LoggerInterface::class => function () {
        // Return empty instance
        return new NullLogger();
    },
];
