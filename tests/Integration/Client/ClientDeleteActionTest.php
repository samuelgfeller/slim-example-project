<?php


namespace App\Test\Integration\Client;


use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\FixtureTestTrait;
use App\Test\Traits\RouteTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;

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
    use FixtureTestTrait;

    /**
     * Test delete client submit with different authenticated user roles.
     *
     * @dataProvider \App\Test\Provider\Client\ClientDeleteCaseProvider::provideUsersForClientDelete()
     *
     * @param array $userLinkedToClientAttr client owner attributes containing the user_role_id
     * @param array $authenticatedUserAttr authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, bool if db_entry_created and json_response
     * @return void
     */
    public function testClientSubmitDeleteAction_authenticated(
        array $userLinkedToClientAttr,
        array $authenticatedUserAttr,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $authenticatedUserRow = $this->insertFixturesWithAttributes($authenticatedUserAttr, UserFixture::class);
        if ($authenticatedUserAttr === $userLinkedToClientAttr) {
            $userLinkedToClientRow = $authenticatedUserRow;
        } else {
            // If authenticated user and owner user is not the same, insert owner
            $userLinkedToClientRow = $this->insertFixturesWithAttributes($userLinkedToClientAttr, UserFixture::class);
        }

        // Insert client status
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
        // Insert client linked to given user
        $clientRow = $this->insertFixturesWithAttributes(
            ['client_status_id' => $clientStatusId, 'user_id' => $userLinkedToClientRow['id']],
            ClientFixture::class
        );

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

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
    public function testClientSubmitDeleteAction_unauthenticated(): void
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
