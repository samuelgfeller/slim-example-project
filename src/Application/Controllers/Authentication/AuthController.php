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
        $data = $request->getParsedBody();

        $userData = [
            'email' => $data['email'],
            'password1' => $data['password1'],
            'password2' => $data['password2'],
        ];
        //validate email/pw
        $user = $this->userService->findUserByEmail($userData['email']);
        $this->logger->info('users/' . $user . ' has been called');


        //get from db
        //if success create token

        $tokenId    = base64_encode(random_bytes (32));
        $issuedAt   = time();
        $notBefore  = $issuedAt + 10;             //Adding 10 seconds
        $expire     = $notBefore + 60;            // Adding 60 seconds

        $data = [
            'iat'  => $issuedAt,         // Issued at: time when the token was generated
            'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss'  => "MyApp",       // Issuer
            'nbf'  => $notBefore,        // Not before
            'exp'  => $expire,           // Expire
            'data' => [                  // Data related to the signer user
                //'userId'   => $rs['id'], // userid from the users table
                'email' => $userData['email'],
            ]
        ];

        $token = JWT::encode($data, "test", "HS256"); //change test to settings
        return $this->respondWithJson($response, $token,200);



    }


}
