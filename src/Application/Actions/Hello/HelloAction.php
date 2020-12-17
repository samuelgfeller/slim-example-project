<?php

namespace App\Application\Actions\Hello;

use App\Application\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class HelloAction
{
    /**
     * @var Responder
     */
    private Responder $responder;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     */
    public function __construct(Responder $responder)
    {
        $this->responder = $responder;
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $name = $args['name'] ?? 'noname';
        $array = [];
//        $a = $test;
//        $array['nothing'];
//        $GLOBALS['_1warning'] = true;

        return $this->responder->render($response, 'hello/hello.html.php', ['name' => $name]);
    }
}
