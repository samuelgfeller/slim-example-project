# Slim api template

Description coming soon
  
  
### settings.php
```php
<?php
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => true, // Should be set to false in production
            'logger' => [
                'name' => 'merge-bs',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                'level' => Logger::DEBUG,
            ],
            'db' => [
                'host' => 'localhost',
                'database' => 'slim_api_skeleton',
                'user' => 'root',
                'pass' => '',
            ],
        ],
    ]);
};
```
