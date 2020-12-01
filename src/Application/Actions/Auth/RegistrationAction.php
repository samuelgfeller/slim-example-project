<?php

/**
 * Action because it is used by many different modules
 * and Controller.php is an abstract class
 */

namespace App\Application\Actions\Auth;

use App\Domain\Exceptions\ValidationException;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;

final class RegistrationAction
{
    protected UserService $userService;
    protected LoggerInterface $logger;


    public function __construct(LoggerInterface $logger, UserService $userService) {
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        // If a html form name changes, these changes have to be done in the Entities constructor
        // too since these names will be the keys from the ArrayReader
        $userData = $request->getParsedBody();

        // Use Entity instead of DTO for simplicity https://github.com/samuelgfeller/slim-api-example/issues/2#issuecomment-597245455
        $user = new User(new ArrayReader($userData));
        // Password gets hashed in service createUser($user) but is needed plain to build up login request body
        $plainPass = $user->getPassword();
        try {
            $insertId = $this->userService->createUser($user);
        } catch (ValidationException $exception) {
            return $this->respondValidationError($exception->getValidationResult(), $response);
        }

        // Log user in
        if (null !== $insertId) {
            $this->logger->info('User "' . $userData['email'] . '" created');

            // Add email and password like it is expected in the login function
            $request = $request->withParsedBody(['email' => $userData['email'], 'password' => $plainPass]);
            // Call login function to authenticate the user
            // todo check if that is good practice or bad
            $loginResponse = $this->login($request, $response);

            $loginContent = json_decode($loginResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);

            // Clear response body after body content is saved
            $response = new \Slim\Psr7\Response();

            $responseContent = $loginContent;

            // maybe there is already a message so it has to be transformed as array
            $responseContent['message'] = [$loginContent['message']];
            $responseContent['message'][] = 'User created and logged in';

            return $this->respondWithJson($response, $responseContent);
        }
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'User could not be registered']);
    }
}