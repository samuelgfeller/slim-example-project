<?php

namespace App\Test\Integration\Dashboard;

use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\UserFilterSettingFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

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
        $loggedInUserId = $this->insertFixturesWithAttributes([], new UserFixture())['id'];
        // Insert other users to have different user filter chips (to test logic for user activity panel)
        $userId = $this->insertFixturesWithAttributes(['first_name' => 'Andrew'], new UserFixture())['id'];
        // Set user Andrew to active filter
        $this->insertFixturesWithAttributes(
            ['module' => 'dashboard-user-activity', 'filter_id' => "user_$userId", 'user_id' => $loggedInUserId],
            new UserFilterSettingFixture()
        );
        // Insert another inactive user
        $this->insertFixturesWithAttributes(['first_name' => 'Mike'], new UserFixture());

        // A dashboard panel is for the status "action pending" and its id is retrieved by the code
        $this->insertFixturesWithAttributes(['name' => 'Action pending'], new ClientStatusFixture());

        // Simulate logged-in user with logged-in user id
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
