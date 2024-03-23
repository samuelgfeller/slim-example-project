<?php

namespace App\Test\Integration\Client;

use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

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
    public function testClientReadPageActionAuthenticated(): void
    {
        // Add needed database values to correctly display the page
        // Insert authenticated user permitted to see client read page
        $userId = $this->insertFixture(new UserFixture())['id'];
        // Insert linked client status
        $clientStatusId = $this->insertFixture(new ClientStatusFixture())['id'];
        // Insert client linked to user to be sure that the user is permitted to see the client read page
        $clientRow = $this->insertFixture(
            new ClientFixture(),
            ['user_id' => $userId, 'client_status_id' => $clientStatusId],
        );

        $request = $this->createRequest('GET', $this->urlFor('client-read-page', ['client_id' => $clientRow['id']]));
        // Simulate logged-in user by setting the user_id session variable
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
        $requestRoute = $this->urlFor('client-read-page', ['client_id' => '1']);
        $request = $this->createRequest('GET', $requestRoute);
        $response = $this->app->handle($request);
        // Assert 302 Found redirect to login url
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $requestRoute]);
        self::assertSame($expectedLoginUrl, $response->getHeaderLine('Location'));
    }
}
