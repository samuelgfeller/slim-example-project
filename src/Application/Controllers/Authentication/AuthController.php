<?php

namespace App\Application\Controllers\Authentication;


use App\Application\Controllers\Controller;
use App\Domain\Auth\AuthService;
use App\Domain\Auth\JwtService;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use App\Domain\Utility\ArrayReader;
use DateTime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Firebase\JWT\JWT;

use const http\Client\Curl\Features\LARGEFILE;

/**
 * Class AuthController
 */
class AuthController extends Controller
{
    protected UserService $userService;
    protected JwtService $jwtService;
    protected AuthService $authService;

    public function __construct(LoggerInterface $logger, UserService $userService,
        JwtService $jwtService, AuthService $authService)
    {
        parent::__construct($logger);
        $this->userService = $userService;
        $this->jwtService = $jwtService;
        $this->authService = $authService;
    }

}
