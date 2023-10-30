<?php

namespace App\Test\Integration\Authentication;

use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\RouteTestTrait;

class LoginPageActionTest extends TestCase
{
    use AppTestTrait;
    use RouteTestTrait;
    use FixtureTestTrait;

    /**
     * Test that login page loads successfully when not authenticated.
     *
     * @return void
     */
    public function testLoginPageAction(): void
    {
        $request = $this->createRequest('GET', $this->urlFor('login-page'));
        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    /**
     * Test login page when already authenticated with redirect link.
     *
     * @return void
     */
    public function testLoginPageActionAlreadyLoggedIn(): void
    {
        // Insert authenticated user
        $userId = $this->insertFixturesWithAttributes([], UserFixture::class)['id'];
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $userId);
        // Prepare route to test the case when the user clicks on a login link with a redirect route
        $requestRouteAfterLogin = $this->urlFor('client-read-page', ['client_id' => 1]);
        // Create request to the login page
        $request = $this->createRequest(
            'GET',
            $this->urlFor('login-page', [], ['redirect' => $requestRouteAfterLogin])
        );
        $response = $this->app->handle($request);
        // Assert 302 Found (redirect)
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        self::assertSame($requestRouteAfterLogin, $response->getHeaderLine('Location'));
    }
}
