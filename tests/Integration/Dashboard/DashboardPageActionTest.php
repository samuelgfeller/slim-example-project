<?php

namespace App\Test\Integration\Dashboard;

use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\UserFilterSettingFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

class DashboardPageActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;

    public function testDashboardPageActionAuthenticated(): void
    {
        // Insert authenticated user
        $loggedInUserId = $this->insertFixture(UserFixture::class)['id'];
        // Insert other users to have different user filter chips (to test logic for user activity panel)
        $userId = $this->insertFixture(UserFixture::class, ['first_name' => 'Andrew'])['id'];
        // Set user Andrew to active filter
        $this->insertFixture(
            UserFilterSettingFixture::class,
            ['module' => 'dashboard-user-activity', 'filter_id' => "user_$userId", 'user_id' => $loggedInUserId],
        );
        // Insert another inactive user
        $this->insertFixture(UserFixture::class, ['first_name' => 'Mike']);

        // A dashboard panel is for the status "action pending" and its id is retrieved by the code
        $this->insertFixture(ClientStatusFixture::class, ['name' => 'Action pending']);

        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createRequest('GET', $this->urlFor('home-page'));
        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testDashboardPageActionUnauthenticated(): void
    {
        // Request route to client read page while not being logged in
        $requestRoute = $this->urlFor('home-page');
        $request = $this->createRequest('GET', $requestRoute);
        $response = $this->app->handle($request);
        // Assert 302 Found redirect to login url
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
        // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $requestRoute]);
        self::assertSame($expectedLoginUrl, $response->getHeaderLine('Location'));
    }
}
