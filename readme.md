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
                'name' => 'event-log',
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

### Returning an error
Backend SHOULD return `"message":"errorMsg"` which MUST be a JSON response so the frontend has information 
about the reason of the fail.   
The javascript function `handleFail(xhr)` checks for `xhr.responseJSON.message` **if it finds it** so not 
mandatory but is be good in some situations for clarity and user experience. 