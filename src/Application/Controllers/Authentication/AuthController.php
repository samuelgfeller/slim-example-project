<?php

namespace App\Controller;


use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use DateTime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

/**
 * Class AuthController
 */
class AuthController
{
    protected $userService;
    protected $userValidation;

    public function __construct(LoggerInterface $logger, UserService $userService, UserValidation $userValidation) {
        parent::__construct($logger);
        $this->userService = $userService;
        $this->userValidation = $userValidation;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $user = $this->userService->findUser($id);
//        var_dump($this->container->get('logger'));
//        $response->getBody()->write('GET request');

        $this->logger->info('users/' . $id . ' has been called');
//        var_dump($this->logger);
        return $this->respondWithJson($response, $user);
    }

    // Björn
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->encoder->encode($request, $response, 'Auth/index.html.twig');
    }

    // björn
    public function login(Request $request, Response $response): Response
    {
        $data = new ArrayReader($request->getParsedBody());
        $username = $data->findString('username');
        $password = $data->findString('password');

        try {
            if (is_email($username)) {
                $userId = $this->userRepository->getIdBy('email', $username);
            } else {
                $userId = $this->userRepository->getIdBy('username', $username);
            }

            $canLogin = $this->auth->canLogin($userId, $password);
            if (!$canLogin) {
                $this->logger->info('Authentication failed for ' . $username . ' because of an invalid password');

                return $this->encoder->encode($request, $response, 'Auth/index.html.twig',
                    ['error' => __('Username or password invalid')]);
            }
        } catch (RecordNotFoundException $exception) {
            $this->logger->info('Authentication failed for ' . $username . '  because of an invalid username');

            return $this->encoder->encode($request, $response, 'Auth/index.html.twig',
                ['error' => __('Username or password invalid')]);
        }

        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $session->set(SessionKey::AUTHENTICATED, true);

        $this->auth->setLastLoginNow($userId);

        return $this->redirect->encode($response, '/admin');
    }


}
