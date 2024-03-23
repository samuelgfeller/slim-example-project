<?php

namespace App\Test\Integration\Authentication;

use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

class LoginPageActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
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
        $userId = $this->insertFixture(new UserFixture())['id'];
        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $userId);
        // Prepare route to test the case when the user clicks on a login link with a redirect route
        $requestRouteAfterLogin = $this->urlFor('client-read-page', ['client_id' => '1']);
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
