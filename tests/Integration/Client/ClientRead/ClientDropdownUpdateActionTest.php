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
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * Test cases for client read page load
 *  - Authenticated
 *  - Unauthenticated
 */
class ClientDropdownUpdateActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use HttpJsonTestTrait;
    use FixtureTrait;

    /**
     * Test that dropdown values client status and assigned user
     * can be changed when authenticated. Any user role can do this.
     * More assertions and with validation for each field is in ClientUpdateActionTest
     *
     * @return void
     */
    public function testClientReadPageAction_authenticated(): void
    {
        // Add needed database values to correctly display the page
        $clientData = (new ClientFixture())->records[0];
        // Insert user linked to client and user that is logged in
        $this->insertFixtureWhere(['id' => $clientData['user_id']], UserFixture::class);
        // Insert linked status
        $this->insertFixtureWhere(['id' => $clientData['client_status_id']], ClientStatusFixture::class);
        // Insert client that should be displayed
        $this->insertFixture('client', $clientData);
        // Insert other user and client status to be changed
        $newUserId = $clientData['user_id'] + 1;
        $this->insertFixtureWhere(['id' => $newUserId], UserFixture::class);
        $newClientStatusId = $clientData['client_status_id'] + 1;
        $this->insertFixtureWhere(['id' => $newClientStatusId], ClientStatusFixture::class);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('client-submit-update', ['client_id' => 1]),
            [
                // Change user and client status id to 1 more than current
                'user_id' => $newUserId,
                'client_status_id' => $newClientStatusId,
            ]
        );
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $clientData['user_id']);

        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->assertTableRow([
            'user_id' => $newUserId,
            'client_status_id' => $newClientStatusId
        ], 'client', $clientData['id']);

        // Assert json response
        $expectedResponseJson = [
            'status' => 'success',
            'data' => null,
        ];
        $this->assertJsonData($expectedResponseJson, $response);
    }

    /**
     * Test that dropdown values client status and assigned user
     * can be changed when authenticated. Any user role can do this.
     *
     * @return void
     */
    public function testClientReadDropdownValueChange_unauthenticated(): void
    {
        // Request route to client read page while not being logged in
        $requestRoute = $this->urlFor('client-submit-update', ['client_id' => 1]);
        $request = $this->createJsonRequest('PUT', $requestRoute, ['user_id' => 2, 'client_status_id' => 2]);
        // Create url where client should be redirected to after login
        $redirectToUrlAfterLogin = $this->urlFor('client-read-page', ['client_id' => 1]);
        $request = $request->withAddedHeader('Redirect-to-url-if-unauthorized', $redirectToUrlAfterLogin);
        // Make request
        $response = $this->app->handle($request);
        // Assert 302 Found redirect to login url
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $requestRoute]);
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }
}