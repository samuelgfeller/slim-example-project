<?php


namespace App\Test\Integration\Client;


use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\FixtureTrait;
use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\PostFixture;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use App\Test\Traits\RouteTestTrait;

/**
 * Client submit delete action tests
 *  - Authenticated with different user roles
 *  - Unauthenticated
 */
class ClientDeleteActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTrait;

    /**
     * Test user delete client with different roles
     *
     * @dataProvider \App\Test\Provider\Client\ClientDeleteCaseProvider::provideUsersForClientDelete()
     * @return void
     */
    public function testClientDeleteAction_authenticated(
        array $userDataLinkedToClient,
        array $authenticatedUserData,
        array $expectedResult
    ): void {
        $authenticatedUserData['id'] = (int)$this->insertFixture('user', $authenticatedUserData);
        // If authenticated user and user that should be linked to client is different, insert both
        if ($userDataLinkedToClient['user_role_id'] !== $authenticatedUserData['user_role_id']) {
            $userDataLinkedToClient['id'] = (int)$this->insertFixture('user', $userDataLinkedToClient);
        } else {
            $userDataLinkedToClient['id'] = $authenticatedUserData['id'];
        }

        // Insert client status
        $clientStatusData = $this->insertFixturesWithAttributes([], ClientStatusFixture::class);
        // Insert client linked to given user
        $clientRow = $this->insertFixturesWithAttributes(
            ['client_status_id' => $clientStatusData['id'], 'user_id' => $userDataLinkedToClient['id']],
            ClientFixture::class
        );

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserData['id']);

        $request = $this->createJsonRequest(
            'DELETE',
            // Post delete route with id like /posts/1
            $this->urlFor('client-submit-delete', ['client_id' => $clientRow['id']]),
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // Assert database
        if ($expectedResult['db_changed'] === true) {
            // Assert that deleted_at is NOT null
            self::assertNotNull($this->getTableRowById('client', $clientRow['id'], ['deleted_at']));
        } else {
            // If db is not expected to change, data should remain the same as when it was inserted from the fixture
            $this->assertTableRow(['deleted_at' => null], 'client', $clientRow['id']);
        }

        // Assert response json content
        $this->assertJsonData($expectedResult['json_response'], $response);
    }


    /**
     * Test that when user is not logged in 401 Unauthorized is returned
     * and that the authentication middleware provides the correct login url
     * if Redirect-to-route-name-if-unauthorized header is set
     *
     * @return void
     */
    public function testClientDeleteAction_unauthenticated(): void
    {
        $request = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('client-submit-delete', ['client_id' => 1]),
        );

        // Provide redirect to if unauthorized header to test if UserAuthenticationMiddleware returns correct login url
        $redirectAfterLoginRouteName = 'client-list-page';
        $request = $request->withAddedHeader('Redirect-to-route-name-if-unauthorized', $redirectAfterLoginRouteName);

        $response = $this->app->handle($request);

        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $this->urlFor($redirectAfterLoginRouteName)]
        );

        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }


    // Unchanged content test not done as it's not being used by the frontend
    // Malformed request body also not so relevant as there is no body for deletion
    // Invalid data not relevant either as there is no data in the request body

}
