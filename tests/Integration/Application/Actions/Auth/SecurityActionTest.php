<?php

namespace App\Test\Integration\Application\Actions\Auth;

use App\Domain\Security\SecurityException;
use App\Test\AppTestTrait;
use App\Test\Fixture\RequestTrackFixtureLoginFailure;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * In this class all actions that are processed by the `SecurityService` are tested.
 */
class SecurityActionTest extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait;
    use RouteTestTrait;

    /**
     * If login request amount exceeds threshold, the user has to wait a certain delay
     */
    public function testTooManyLoginAttempts(): void
    {
        // Per default not set when script executed with cli
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // Insert max amount of allowed failed requests
        $this->insertFixtures([RequestTrackFixtureLoginFailure::class]);

        // One request too much
        $request = $this->createFormRequest(
            'POST',
            $this->urlFor('login-submit'),
            // Same keys than HTML form
            [
                'email' => 'toomanylogin.attempts@security.com',
                'password' => '12345678',
            ]
        );

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Exceeded maximum of tolerated login requests.');

        // In try catch to assert exception attributes
        try {
            $response = $this->app->handle($request);
        } catch (SecurityException $se) {
            self::assertSame(SecurityException::USER_LOGIN, $se->getType());
            // Throw because it's expected to verify that exception is thrown
            throw $se;
        }
    }
}
