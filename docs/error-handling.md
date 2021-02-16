# Error handling 
![Error page](https://i.imgur.com/9KIW0Qq.png) 

## Configuration
### Default values
The following configuration values are in `defaults.php` but can and should be changed
in the environment specific config file.   
File: `defaults.php` 
```php
// Error handler
$settings['error'] = [
    // Should be set to false in production
    'display_error_details' => false,
    // Should be set to false for unit tests
    'log_errors' => true,
    // Display error details in error log
    'log_error_details' => true,
];
```
### Environment specific values 
#### Development
While developing errors, warning and notices should be displayed to the developer.  
File: `env.php`   
```php
// Error handler
$settings['error']['display_error_details'] = true;
```
#### Production
In production errors, warnings and notices should be logged but details not shown
to client.  
File: `env.php` 
```php
// Error handler
$settings['error']['display_error_details'] = false;
```

## The way PHP handles errors
Like the [excellent article](https://phptherightway.com/#errorsandexceptions) from 
PHP The Right Way tells us, PHP errors can be divided in two main categories with a 
different behaviour.  

### Fatal Errors 
Main [error severity](https://www.php.net/manual/en/errorfunc.constants.php): `E_ERROR`   
Happen when: **Exception** is thrown or for e.g. a **non-existent function** gets called.  
Impact: **stop execution** of PHP code, script halted   

### Warnings and Notices   
Main error severity: `E_NOTICE` and `E_WARNING`   
Happen when: for e.g. use of undefined variable, undefined array index   
Impact: PHP will try to keep processing, script not halted

### Default error reporting
Per default errors are logged in the default webserver `error.log` and details 
are shown to the user either by the default php error message, or your framework default 
error handler or your debugging tool (for e.g. [Xdebug](https://i.stack.imgur.com/v2IPn.png)).  

To have a custom look, we need to create and assign a custom-made `ErrorHanlder` to 
warning and notice type errors and fatal errors.  

### Why make a custom handler?
There are a few points
* Even though I love how xdebug displays errors so clearly I dislike the reversed order 
of the stack trace and the oldish looking table.  
* Also, I want all errors to be logged in the project log file and not default `error.log`.  
* Slim has an ErrorHandler, but I don't like it. It doesn't highlight important parts.  

With a custom handler I could display what I want, in the style I want and log the errors
where I want.

### How to get stacktrace and more infos from warnings
To get the details of an error that is not fatal, the [best way](https://stackoverflow.com/questions/6426758/php-log-stacktrace-for-warnings) 
is to transform it into an Exception. More specifically an ErrorException. [Docs about it](https://phptherightway.com/#ErrorException).  
This can be done in a function inside `set_error_handler()`. Example [here](https://www.php.net/manual/en/class.errorexception.php)    
It's important to note that unless caught, any Exception thrown will halt the script so even 
something harmless like `E_USER_NOTICE ` will do that.   
This was one of my concerns and at first I tried to create the error, pass it through my 
`ErrorHandler` and display the notice / warning at the end of the execution. (Explained 
[here](https://samuel-gfeller.atlassian.net/browse/SLE-57?focusedCommentId=10130). 
Contact me if interested.) As I was exasperating I discovered that big frameworks like 
Symfony and Laravel do exactly that in development, and I can just set `display_error_details` 
to `false` in production and not throw the error but still log them.   
One could even argue that it's preferable to develop this way and be exception-heavy like 
other languages e.g. Python. 

  
## Middlewares
To control the whole scope of [PHP errors](https://www.php.net/manual/en/errorfunc.constants.php) 
two middlewares are needed. One `ErrorHandlerMiddleware` which logs and throws the errors in 
development and an `ErrorMiddleware` which passes the fatal-error to the handler 
which builds the response to the client.  

### Warning and notice middleware
This middleware logs all non-fatal errors and when `display_error_details` is `true` an 
`ErrorException` is thrown. Which will later be caught by the `ErrorMiddleware` and passed
to the error handler.
  
File: `src/Application/Middleware/ErrorHandlerMiddleware.php`
```php
namespace App\Application\Middleware;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Middleware which sets set_error_handler() to custom DefaultErrorHandler
 * and logs warning and notices
 */
final class ErrorHandlerMiddleware implements MiddlewareInterface
{

    private bool $displayErrorDetails;

    private bool $logErrors;

    private LoggerInterface $logger;

    /**
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param LoggerInterface $logger
     */
    public function __construct(
        bool $displayErrorDetails,
        bool $logErrors,
        LoggerInterface $logger
    ) {
        $this->displayErrorDetails = $displayErrorDetails;
        $this->logErrors = $logErrors;
        $this->logger = $logger;
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     * @throws ErrorException
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Only make notices / wantings to ErrorException's if error details should be displayed
        // SLE-57 Making warnings and notices to exceptions in dev
        // set_error_handler does not handle fatal errors so this function gets not called by fatal errors
        set_error_handler(
            function ($severity, $message, $file, $line) {
                if (error_reporting() & $severity) {
                    // Log all non fatal errors
                    if ($this->logErrors) {
                        // If error is warning
                        if ($severity === E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING) {
                            $this->logger->warning(
                                "Warning [$severity] $message on line $line in file $file"
                            );
                        } // If error is non fatal but not warning (default)
                        else {
                            $this->logger->notice(
                                "Notice [$severity] $message on line $line in file $file"
                            );
                        }
                    }
                    // Throwing an exception allows us to have a stack trace and more error details useful in dev
                    if ($this->displayErrorDetails === true) {
                        // Logging for fatal errors happens in DefaultErrorHandler.php
                        throw new ErrorException($message, 0, $severity, $file, $line);
                    }
                }
                return true;
            }
        );

        return $handler->handle($request);
    }
}
```
Add the container definition.  
File: `app/container/container.php`  
```php
use App\Application\Middleware\ErrorHandlerMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [

    // ... 

    ErrorHandlerMiddleware::class => function (ContainerInterface $container) {
        $config = $container->get('settings')['error'];
        $logger = $container->get(LoggerInterface::class);
        return new ErrorHandlerMiddleware(
            (bool)$config['display_error_details'],
            (bool)$config['log_errors'],
            $logger,
        );
    },
];
```
Add to the middleware stack.  
File: `app/middleware.`
```php
use Slim\App;
use App\Application\Middleware\ErrorHandlerMiddleware;

return function (App $app) {

    // ...
    $app->add(ErrorHandlerMiddleware::class); // <-- here
};
```

### Fatal error middleware
The `ErrorMiddleware` is in charge of catching a fatal error if there is one
and pass the `Exception` to the custom `DefaultErrorHandler.php`.   

Slim has a middleware that is perfectly capable to do that. I won't show the entire 
code but the `process` method (which is called by default) is interesting.   
Middlewares are the last things that are called before sending the response to the client and 
this one is the last middleware to be called. If an error occurred that hasn't been caught 
anywhere before it will finally be caught here.   
The exception then is passed to the handler wich can work with it.   
File: `vendor/slim/slim/Slim/Middleware/ErrorMiddleware.php`
```php
// ...

// function process opening 
    try {
        return $handler->handle($request);
    } catch (Throwable $e) {
        return $this->handleException($request, $e);
    }
// function process closing

// ...
```

Add the container definition with the right arguments.   
File: `app/container/container.php`  
```php
use App\Application\Handler\DefaultErrorHandler;
use App\Application\Middleware\ErrorHandlerMiddleware;
use Psr\Container\ContainerInterface;
use Slim\Middleware\ErrorMiddleware;
use Psr\Log\LoggerInterface;
use Slim\App;

return [

    // ...

    // Added previously
    ErrorHandlerMiddleware::class => function (ContainerInterface $container) {...},
    
    // New 
    ErrorMiddleware::class => function (ContainerInterface $container) {
        $config = $container->get('settings')['error'];
        $app = $container->get(App::class);

        $logger = $container->get(LoggerInterface::class);

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
];
```

This should be the last middleware added since anything that happens afterwards will not
be handled by this middleware.   
File: `app/middleware.`
```php
use Slim\App;
use App\Application\Middleware\ErrorHandlerMiddleware;
use Slim\Middleware\ErrorMiddleware;

return function (App $app) {

    // ...

    $app->add(ErrorHandlerMiddleware::class); 
    $app->add(ErrorMiddleware::class); // <-- last middleware 
};
``` 

## Error Handler
Finally, the error handler, the core of all this.  
`__invoke` is called from `ErrorMiddleware` and exception along with config values are given as 
arguments.  
First of all, fatal errors are logged. For this we have to make sure that exception is not 
an instance of `ErrorException` because that would mean it's a warning / notice, and they
were already logged in the `ErrorHandlerMiddleware`.    

After that, the response body is created.   
If `$displayErrorDetails` is `false` the error message is made out of only the error code 
and the reason phrase (e.g. *500 Internal Error* or *404 Not found* ). This is then 
displayed in a nice user-friendly page.  
If `$displayErrorDetails` is `true` an HTML page is built with the exception details including
the stack trace. I like to have it displayed in a clear and pretty format, so I added a lot of
CSS. E.g. paths in the stack trace that don't contain the word `vender` are emphasised because
they are much more relevant to me. I also like to make a distinction from a warning / notice 
and I do that with the color red and orange. 

File: `src/Application/Handler/DefaultErrorHandler.php` (most up to date version can be
found [here](https://github.com/samuelgfeller/slim-example-project/blob/master/src/Application/Handler/DefaultErrorHandler.php))
```php
<?php

namespace App\Application\Handler;

use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Slim\Views\PhpRenderer;
use Throwable;

/**
 * Default Error Renderer.
 */
class DefaultErrorHandler
{
    /**
     * @var PhpRenderer
     */
    private PhpRenderer $phpRenderer;

    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param PhpRenderer $phpRenderer PHP-View renderer
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param LoggerFactory $logger Logger
     */
    public function __construct(
        PhpRenderer $phpRenderer,
        ResponseFactoryInterface $responseFactory,
        LoggerFactory $logger
    ) {
        $this->phpRenderer = $phpRenderer;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger->addFileHandler('error.log')->createInstance('error');
    }

    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request The request
     * @param Throwable $exception The exception
     * @param bool $displayErrorDetails Show error details
     * @param bool $logErrors Log errors
     *
     * @return ResponseInterface The response
     * @throws Throwable
     */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors
    ): ResponseInterface {
        // Log error
        // ErrorException was configured to be thrown with set_error_handler which is for non-fatal errors
        // They are logged in ErrorHandlerMiddleware.php and not here because if displayErrorDetails is false
        // ErrorException is not thrown and they wouldn't be logged in prod
        if ($logErrors && !$exception instanceof \ErrorException) {
            // Error with no stack trace https://stackoverflow.com/a/2520056/9013718
            $this->logger->error(
                sprintf(
                    'Error: [%s] %s File %s:%s , Method: %s, Path: %s',
                    $exception->getCode(),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine(),
                    $request->getMethod(),
                    $request->getUri()->getPath()
                )
            );
        }
        
        // Error output if script is called via cli (e.g. testing)
        if (PHP_SAPI === 'cli') {
            // The exception is thrown to have the standard behaviour (important for testing)
            throw $exception;
        }

        // Detect status code
        $statusCode = $this->getHttpStatusCode($exception);
        $reasonPhrase = $this->responseFactory->createResponse()->withStatus($statusCode)->getReasonPhrase();

        // Depending on displayErrorDetails different error infos will be shared
        if ($displayErrorDetails === true) {
            $errorMessage = $this->getExceptionDetailsAsHtml($exception, $statusCode, $reasonPhrase);
            $errorTemplate = 'error/error-details.html.php'; // If this path fails, the default exception is shown
            // Layout removed in error detail template
        } else {
            $errorMessage = ['statusCode' => $statusCode, 'reasonPhrase' => $reasonPhrase];
            $errorTemplate = 'error/error-page.html.php';
        }

        // Create response
        $response = $this->responseFactory->createResponse();

        // Render template
        $response = $this->phpRenderer->render(
            $response,
            $errorTemplate,
            ['errorMessage' => $errorMessage,]
        );

        return $response->withStatus($statusCode);
    }

    /**
     * Get http status code.
     *
     * @param Throwable $exception The exception
     *
     * @return int The http code
     */
    private function getHttpStatusCode(Throwable $exception): int
    {
        // Detect status code
        $statusCode = 500;

        if ($exception instanceof HttpException) {
            $statusCode = (int)$exception->getCode();
        }

        if ($exception instanceof \DomainException || $exception instanceof InvalidArgumentException) {
            // Bad request
            $statusCode = 400;
        }

        if ($exception instanceof ValidationException) {
            // Unprocessable Entity
            $statusCode = 422;
        }

        $file = basename($exception->getFile());
        if ($file === 'CallableResolver.php') {
            $statusCode = 404;
        }

        return $statusCode;
    }

    /**
     * Build HTML with exception content and styling
     *
     * @param Throwable $exception Error
     *
     * @param int|null $statusCode
     * @param string|null $reasonPhrase
     * @return string The full error message
     */
    private function getExceptionDetailsAsHtml(
        Throwable $exception,
        int $statusCode = null,
        string $reasonPhrase = null
    ): string {
        // Init variables
        $error = '';

        $file = $exception->getFile();
        $line = $exception->getLine();
        $message = $exception->getMessage();
        $trace = $exception->getTrace();


        // Check if it is a warning message or error
        $errorCssClass = $exception instanceof \ErrorException ? 'warning' : 'error';

        // prepare path to be more readable https://stackoverflow.com/a/9891884/9013718
        $lastBackslash = strrpos($file, '\\');
        $lastWord = substr($file, $lastBackslash + 1);
        $firstChunkFullPath = substr($file, 0, $lastBackslash + 1);
        // remove C:\xampp\htdocs\ and project name to keep only part starting with src\
        $firstChunkMinusFilesystem = str_replace('C:\xampp\htdocs\\', '', $firstChunkFullPath);
        // locate project name because it is right before the first backslash (after removing filesystem)
        $projectName = substr($firstChunkMinusFilesystem, 0, strpos($firstChunkMinusFilesystem, '\\') + 1);
        // remove project name from first chunk
        $firstChunk = str_replace($projectName, '', $firstChunkMinusFilesystem);

        // build error html page
        $error .= sprintf('<body class="%s">', $errorCssClass); // open body
        $error .= sprintf('<div id="title-div" class="%s">', $errorCssClass); // opened title div
        if ($statusCode !== null && $reasonPhrase !== null) {
            $error .= sprintf(
                '<p><span>%s | %s</span><span id="exception-name">%s</span></p>',
                $statusCode,
                $reasonPhrase,
                get_class($exception)
            );
        }
        $error .= sprintf(
            '<h1>%s in <span id="first-path-chunk">%s </span>%s on line %s.</h1></div>', // closed title div
            $message,
            $firstChunk,
            $lastWord,
            $line
        ); // close title div

        $error .= sprintf('<div id="trace-div" class="%s"><table>', $errorCssClass); // opened trace div / opened table
        $error .= '<tr><th id="num-th">#</th><th>Function</th><th>Location</th></tr>';
        foreach ($trace as $key => $t) {
            // Sometimes class, type, file and line not set e.g. pdfRenderer when var undefined in template
            $t['class'] = $t['class'] ?? '';
            $t['type'] = $t['type'] ?? '';
            $t['file'] = $t['file'] ?? '';
            $t['line'] = $t['line'] ?? '';
            // remove everything from file path before the last \
            $fileWithoutPath = $this->removeEverythingBeforeChar($t['file']);
            // remove everything from class before late \
            $classWithoutPath = $this->removeEverythingBeforeChar($t['class']);
            // if file path has not vendor in it, a css class is added to indicate it because it's more relevant
            $nonVendorClass = !strpos($t['file'], 'vendor') ? 'non-vendor' : '';
            // adding html
            $error .= sprintf(
                '<tr><td>%s</td><td class="function-td %s">%s</td><td class="%s">%s</td></tr>',
                $key,
                $nonVendorClass,
                '...\\' . $classWithoutPath . $t['type'] . $t['function'] . '(...)', // only last 85 chars
                $nonVendorClass,
                '...\\' . $fileWithoutPath . ':<span class="lineSpan">' . $t['line'] . '</span>',
            );
        }
        $error .= '</table></div>'; // close table
        $error .= '<style>
            @font-face { font-family: CenturyGothic; src: url(assets/general/fonts/CenturyGothic.ttf); }
            body { margin: 0; background: #ffd9d0; font-family: "CenturyGothic", CenturyGothic, Geneva, AppleGothic, sans-serif; }
            body.warning { background: #ffead0; }
            body.error { background: #ffd9d0; }
            #title-div{ padding: 5px 10%; color: black; margin:30px; background: tomato; border-radius: 0 35px; box-shadow: 0 0 17px tomato; }
            #title-div h1 { margin-top: 4px; }
            #title-div.warning { background: orange; box-shadow: 0 0 17px orange;}
            #title-div.error { background: tomato; box-shadow: 0 0 17px tomato;}
            #first-path-chunk{ font-size: 0.7em; }
            #trace-div{ width: 80%; margin: auto auto 40px; min-width: 688px; padding: 20px; background: #ff9e88; border-radius: 0 35px;
                 box-shadow: 0 0 10px #ff856e; }
            #trace-div.warning { background: #ffc588; box-shadow: 0 0 10px #ffad6e; }
            #trace-div.error { background: #ff9e88; box-shadow: 0 0 10px #ff856e; }
            #trace-div h2{ margin-top: 0; padding-top: 19px; text-align: center; }
            #trace-div table{ border-collapse: collapse;  font-size: 1.2em; width: 100%; overflow-x: auto; }
            #trace-div table td, #trace-div table th{  /*border-top: 6px solid red;*/ padding: 8px; text-align: left;}
            #trace-div table tr td:first-child, #trace-div table tr th:first-child { padding-left: 20px; }
            #num-th { font-size: 2em; color: #a46856; margin-right: 50px;}
            .non-vendor{ font-weight: bold; } 
            .non-vendor .lineSpan{ font-weight: bold; color: #b00000;font-size: 1.3em; } 
            #exception-name { float: right}
            @media screen and (max-width: 1000px) {
                #trace-div { font-size: 0.8em; }
                #title-div h1 { font-size: 1.6em; }
            }
            @media screen and (max-width: 810px) {
                #trace-div table { font-size: 1.1em; }
                #title-div { box-sizing: border-box; margin-left: 0; margin-right: 0; width: 100%; }
            }
            
            </style>';
        $error .= '</body>'; // close body

        return $error;
    }

    private function removeEverythingBeforeChar(string $string, string $lastChar = '\\'): string
    {
        return trim(substr($string, strrpos($string, $lastChar) + 1));

        // alternative https://coderwall.com/p/cpxxxw/php-get-class-name-without-namespace
//        $path = explode('\\', __CLASS__);
//        return array_pop($path);
    }
}
```

The template is basically just including the default layout and outputting the `$errorMessage`
which is in HTML.   
File: `errpr-detail.html.php`
```php
<?php
/**
 * @var array $errorMessage containing html error page
 */

echo $errorMessage;
```

For the user-friendly error-page a little more styling is added.   
File: `error-page.html.php` code can be found [here](https://github.com/samuelgfeller/documentation/blob/master/html-css/pages/user-friendly-error-page.md).


## Conclusion
This makes debugging a lot more fun for me since the error is displayed in a clear and pretty
format, exactly like I want it to.
  
It's not perfect however and if something breaks inside one of the code blocks that were shown 
above, the default handler will be called and the error will be logged at the default place.
It's important to be aware of this.
