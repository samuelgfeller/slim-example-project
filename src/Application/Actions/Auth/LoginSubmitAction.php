<?php

/**
 * Action because it is used by many different modules
 * and Controller.php is an abstract class
 */

namespace App\Application\Actions\Auth;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Auth\JwtService;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;

final class LoginSubmitAction
{
    protected AuthService $authService;
    protected LoggerInterface $logger;
    protected Responder $responder;
    protected JwtService $jwtService;


    public function __construct(Responder $responder, LoggerInterface $logger, AuthService $authService,
        JwtService $jwtService) {
        $this->responder = $responder;
        $this->authService = $authService;
        $this->logger = $logger;
        $this->jwtService = $jwtService;

    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        // todo add check if already logged in

        $userData = $request->getParsedBody();

        $user = new User(new ArrayReader($userData));

//        echo $userData['wrong']; // notice
//        $variable->method(); // Error
        try {
            // Throws error if not
            $this->authService->GetUserIdIfAllowedToLogin($user);

            $token = $this->jwtService->createToken(['uid' => $user->getEmail()]);

            $lifetime = $this->jwtService->getLifetime();

            // Transform the result into a OAuh 2.0 Access Token Response
            // https://www.oauth.com/oauth2-servers/access-tokens/access-token-response/
            $result = [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $lifetime,
            ];

            $this->logger->info('Successful login from user "' . $user->getEmail() . '"');
            return $this->responder->respondWithJson(
                $response,
                ['token' => json_encode($result), 'status' => 'success', 'message' => 'Logged in'],
                201
            );
        } catch (ValidationException $exception) {
            // Validation error is logged in AppValidation.php
            return $this->responder->respondWithJsonOnValidationError($exception->getValidationResult(), $response);
        } catch (InvalidCredentialsException $e) {
            // Log error
            $this->logger->notice(
                'InvalidCredentialsException thrown with message: "' . $e->getMessage() . '" user "' . $e->getUserEmail(
                ) . '"'
            );

            // Respond to client
            $responseData = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            return $this->responder->respondWithJson($response, $responseData, 401);
        }
    }
}