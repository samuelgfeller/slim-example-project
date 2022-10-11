<?php


namespace App\Test\Integration\Client\ClientRead;


use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use App\Test\Traits\AppTestTrait;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * Test cases for client read page load
 *  - Authenticated
 *  - Unauthenticated
 */
class ClientReadPageActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTrait;

    /**
     * Test that user has to be logged in to display the page
     *
     * @return void
     */
    public function testClientReadPageAction_notLoggedIn(): void
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

    /**
     * Normal page action while being authenticated
     *
     * @return void
     */
    public function testClientReadPageAction_loggedIn(): void
    {
        // Add needed database values to correctly display the page
        $clientData = (new ClientFixture())->records[0];
        // Insert user linked to client and user that is logged in
        $this->insertFixtureWhere(['id' => $clientData['user_id']], UserFixture::class);
        // Insert linked status
        $this->insertFixtureWhere(['id' => $clientData['client_status_id']], ClientStatusFixture::class);
        // Insert client that should be displayed
        $this->insertFixture('client', $clientData);

        $request = $this->createRequest('GET', $this->urlFor('client-read-page', ['client_id' => 1]));
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $clientData['user_id']);

        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }
}