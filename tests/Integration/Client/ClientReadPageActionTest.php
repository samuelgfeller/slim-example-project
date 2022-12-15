<?php

namespace App\Test\Integration\Client;

use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * Test cases for client read page load
 *  - Authenticated
 *  - Unauthenticated.
 */
class ClientReadPageActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;

    /**
     * Normal page action while being authenticated.
     *
     * @return void
     */
    public function testClientReadPageActionAuthorization(): void
    {
        // Insert linked and authenticated user
        $userId = $this->insertFixturesWithAttributes([], UserFixture::class)['id'];
        // Insert linked client status
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
        // Add needed database values to correctly display the page
        $clientRow = $this->insertFixturesWithAttributes(
            ['user_id' => $userId, 'client_status_id' => $clientStatusId],
            ClientFixture::class
        );

        $request = $this->createRequest('GET', $this->urlFor('client-read-page', ['client_id' => $clientRow['id']]));
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $clientRow['user_id']);

        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    /**
     * Test that user has to be logged in to display the page.
     *
     * @return void
     */
    public function testClientReadPageActionUnauthenticated(): void
    {
        // Request route to client read page while not being logged in
        $requestRoute = $this->urlFor('client-read-page', ['client_id' => 1]);
        $request = $this->createRequest('GET', $requestRoute);
        $response = $this->app->handle($request);
        // Assert 302 Found redirect to login url
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $requestRoute]);
        self::assertSame($expectedLoginUrl, $response->getHeaderLine('Location'));
    }
}
