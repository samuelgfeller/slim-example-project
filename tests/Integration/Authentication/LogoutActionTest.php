<?php

namespace App\Test\Integration\Authentication;

use App\Modules\User\Enum\UserStatus;
use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

class LogoutActionTest extends TestCase
{
    use AppTestTrait;
    use FixtureTestTrait;
    use RouteTestTrait;
    use HttpTestTrait;

    public function testLogoutPageAction(): void
    {
        // Insert user fixture
        $user = $this->insertFixture(UserFixture::class);
        // Authenticate user
        $this->container->get(SessionInterface::class)->set('user_id', $user['id']);

        // Create request
        $request = $this->createRequest('GET', $this->urlFor('logout'));
        $response = $this->app->handle($request);

        // Assert: 302 Found (redirect)
        self::assertSame(302, $response->getStatusCode());

        // Assert that session user_id is not set
        self::assertNull($this->container->get(SessionInterface::class)->get('user_id'));
    }

    /**
     * Test that when a user is not active but still logged in and tries
     * to access a protected route, the user is logged out.
     *
     * @return void
     */
    public function testLogoutWhenUserNotActive(): void
    {
        // Insert user fixture
        $user = $this->insertFixture(UserFixture::class, ['status' => UserStatus::Suspended->value]);
        // Authenticate user
        $this->container->get(SessionInterface::class)->set('user_id', $user['id']);

        // Create request
        $request = $this->createRequest('GET', $this->urlFor('profile-page'));
        $response = $this->app->handle($request);

        // Assert: 302 Found (redirect)
        self::assertSame(302, $response->getStatusCode());

        // Assert that session user_id is not set
        self::assertNull($this->container->get(SessionInterface::class)->get('user_id'));
    }
}
