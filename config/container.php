<?php

use App\Application\ErrorHandler\DefaultErrorHandler;
use App\Application\Middleware\ErrorHandlerMiddleware;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Settings;
use Cake\Database\Connection;
use Nyholm\Psr7\Factory\Psr17Factory;
use Odan\Session\Middleware\SessionMiddleware;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Views\PhpRenderer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\EventListener\EnvelopeListener;
use Symfony\Component\Mailer\EventListener\MessageListener;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },
    App::class => function (ContainerInterface $container) {
        $app = AppFactory::createFromContainer($container);
        // Register routes
        (require __DIR__ . '/routes.php')($app);

        // Register middleware
        (require __DIR__ . '/middleware.php')($app);

        return $app;
    },
    LoggerFactory::class => function (ContainerInterface $container) {
        return new LoggerFactory($container->get('settings')['logger']);
    },

    // HTTP factories
    // For Responder and error middleware
    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },
    ServerRequestFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },

    // For Responder
    RouteParserInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getRouteCollector()->getRouteParser();
    },

    // Error middlewares
    ErrorHandlerMiddleware::class => function (ContainerInterface $container) {
        $config = $container->get('settings')['error'];
        $logger = $container->get(LoggerFactory::class);

        return new ErrorHandlerMiddleware(
            (bool)$config['display_error_details'],
            (bool)$config['log_errors'],
            $logger,
        );
    },
    ErrorMiddleware::class => function (ContainerInterface $container) {
        $config = $container->get('settings')['error'];
        $app = $container->get(App::class);

        $logger = $container->get(LoggerFactory::class)->addFileHandler('error.log')->createInstance(
            'default-errorhandler'
        );

        $errorMiddleware = new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$config['display_error_details'],
            (bool)$config['log_errors'],
            (bool)$config['log_error_details'],
            $logger
        );

        $errorMiddleware->setDefaultErrorHandler($container->get(DefaultErrorHandler::class));

        return $errorMiddleware;
    },

    // Database
    Connection::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['db'];

        return new Connection($settings);
    },
    PDO::class => function (ContainerInterface $container) {
        $connection = $container->get(Connection::class);
        $connection->getDriver()->connect();

        return $connection->getDriver()->getConnection();
    },
    // Used by command line to generate `schema.sql` for integration testing
    'DatabaseSqlSchemaGenerator' => function (ContainerInterface $container) {
        return new \App\Common\Database\DatabaseSqlSchemaGenerator(
            $container->get(PDO::class),
            $container->get('settings')['root']
        );
    },
    Settings::class => function (ContainerInterface $container) {
        return new Settings($container->get('settings'));
    },

    // Template renderer
    PhpRenderer::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $rendererSettings = $settings['renderer'];

        /** Global attributes are set in @see PhpViewExtensionMiddleware */
        return new PhpRenderer($rendererSettings['path']);
    },

    // Sessions
    SessionInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $session = new PhpSession();
        $session->setOptions((array)$settings['session']);

        return $session;
    },

    SessionMiddleware::class => function (ContainerInterface $container) {
        return new SessionMiddleware($container->get(SessionInterface::class));
    },

    BasePathMiddleware::class => function (ContainerInterface $container) {
        return new BasePathMiddleware($container->get(App::class));
    },

    // SMTP transport
    MailerInterface::class => function (ContainerInterface $container) {
        return new Mailer($container->get(TransportInterface::class));
    },
    // Mailer transport
    TransportInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['smtp'];

        // smtp://user:pass@smtp.example.com:25
        $dsn =
            sprintf(
                '%s://%s:%s@%s:%s',
                $settings['type'],
                $settings['username'],
                $settings['password'],
                $settings['host'],
                $settings['port']
            );

        $eventDispatcher = $container->get(EventDispatcherInterface::class);

        return Transport::fromDsn($dsn, $eventDispatcher);
    },
    // Event dispatcher for mailer
    EventDispatcherInterface::class => function () {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new MessageListener());
        $eventDispatcher->addSubscriber(new EnvelopeListener());
        $eventDispatcher->addSubscriber(new MessageLoggerListener());

        return $eventDispatcher;
    },
];
