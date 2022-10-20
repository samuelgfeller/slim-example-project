<?php


namespace App\Test\Integration\Client;


use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\FixtureTrait;
use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use App\Test\Traits\RouteTestTrait;
use Slim\Exception\HttpBadRequestException;

/**
 * Client update integration test:
 * - normal update
 * - invalid note update
 * - unauthenticated client update
 * - client update request with value to change being the same as in database
 * NOT in this test:
 * - edit non-existing client - reason: delete request on non-existing client is tested
 */
class ClientUpdateActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTrait;

    /**
     * Test that client values can be changed when authenticated.
     * Any user role can do this, so it's not necessary to test them with a data provider.
     * Fixture dependency
     *  - User with id 1 higher than user linked to client
     *  - Client status with id 1 higher than status linked to first client
     *
     * @dataProvider \App\Test\Provider\Client\ClientUpdateCaseProvider::provideUsersAndExpectedResultForClientUpdate()
     *
     * @return void
     */
    public function testClientSubmitUpdateAction_authenticated(
        array $userDataLinkedToClient,
        array $authenticatedUserData,
        array $requestData,
        array $expectedResult
    ): void {
        $this->insertFixture('user', $authenticatedUserData);
        // If authenticated user and user that should be linked to client is different, insert both
        if ($userDataLinkedToClient['id'] !== $authenticatedUserData['id']) {
            $this->insertFixture('user', $userDataLinkedToClient);
        }

        // Get one client data from fixture
        $clientRow = (new ClientFixture())->records[0];
        // Change the linked user to be the given one
        $clientRow['user_id'] = $userDataLinkedToClient['id'];
        // Insert linked status
        $this->insertFixtureWhere(['id' => $clientRow['client_status_id']], ClientStatusFixture::class);
        // Insert client that will be used for this test
        $this->insertFixture('client', $clientRow);

        // Insert other user and client status that are used for the modification request if needed
        if (isset($requestData['user_id'])) {
            // Add 1 to user_id linked to client
            $requestData['user_id'] = $clientRow['user_id'] + 1;
            $this->insertFixtureWhere(['id' => $requestData['user_id']], UserFixture::class);
        }
        if (isset($requestData['client_status_id'])) {
            // Add 1 to client status id
            $requestData['client_status_id'] = $clientRow['client_status_id'] + 1;
            $this->insertFixtureWhere(['id' => $requestData['client_status_id']], ClientStatusFixture::class);
        }

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('client-submit-update', ['client_id' => $clientRow['id']]),
            $requestData
        );

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserData['id']);

        $response = $this->app->handle($request);
        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // Assert database
        if ($expectedResult['db_changed'] === true) {
            // HTML form element names are the same as the database columns, the same request array can be taken to assert the db
            // Check that data in request body was changed
            $this->assertTableRowEquals($requestData, 'client', $clientRow['id']);
        } else {
            // If db is not expected to change, data should remain the same as when it was inserted from the fixture
            $this->assertTableRowEquals($clientRow, 'client', $clientRow['id']);
        }

        $this->assertJsonData($expectedResult['json_response'], $response);
    }

    /**
     * Test client values validation.
     *
     * @dataProvider \App\Test\Provider\Client\ClientCreateUpdateCaseProvider::invalidClientValuesAndExpectedResponseData()
     * @return void
     */
    public function testClientSubmitUpdateAction_invalid($requestBody, $jsonResponse): void
    {
        // Add one client
        $clientRow = (new ClientFixture())->records[0];
        // Insert user linked to client and user that is logged in
        $this->insertFixtureWhere(['id' => $clientRow['user_id']], UserFixture::class);
        // Insert linked status
        $this->insertFixtureWhere(['id' => $clientRow['client_status_id']], ClientStatusFixture::class);
        // Insert client that will be used for this test
        $this->insertFixture('client', $clientRow);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('client-submit-update', ['client_id' => 1]),
            $requestBody
        );

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $clientRow['user_id']);

        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // database should be unchanged
        $this->assertTableRow($clientRow, 'client', $clientRow['id']);

        $this->assertJsonData($jsonResponse, $response);
    }


    /**
     * Test that dropdown values client status and assigned user
     * can be changed when authenticated. Any user role can do this.
     *
     * @return void
     */
    public function testClientSubmitUpdateAction_unauthenticated(): void
    {
        // Request route to client read page while not being logged in
        $requestRoute = $this->urlFor('client-submit-update', ['client_id' => 1]);
        // Request body not important as it shouldn't be taken into account when unauthenticated
        $request = $this->createJsonRequest('PUT', $requestRoute, ['user_id' => 2]);
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

    /**
     * Test that if user makes update request but the content has not changed
     * compared to what's in the database, the response contains the warning.
     *
     * @return void
     */
    public function testClientSubmitUpdateAction_unchangedContent(): void
    {
        // Add one client
        $clientRow = (new ClientFixture())->records[0];
        // Insert user linked to client and user that is logged in
        $this->insertFixtureWhere(['id' => $clientRow['user_id']], UserFixture::class);
        // Insert linked status
        $this->insertFixtureWhere(['id' => $clientRow['client_status_id']], ClientStatusFixture::class);
        // Insert client that will be used for this test
        $this->insertFixture('client', $clientRow);

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $clientRow['user_id']);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('client-submit-update', ['client_id' => 1]),
            // Submitted first name is EXACTLY THE SAME as what's already in the database
            ['first_name' => $clientRow['first_name']]
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that response contains warning
        $this->assertJsonData(['status' => 'warning', 'message' => 'The client was not updated.'], $response);

        $this->assertTableRow(['first_name' => $clientRow['first_name']], 'client', $clientRow['id']);
    }

    /**
     * Test client modification with malformed request body
     *
     * @return void
     */
    public function testClientSubmitUpdateAction_malformedRequest(): void
    {
        // Action class should directly return error so only logged-in user has to be inserted
        $userData = (new UserFixture())->records[0];
        $this->insertFixture('user', $userData);

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userData['id']);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('client-submit-update', ['client_id' => 1]),
            // The update request can format the request body pretty freely as long as it doesn't contain a non-allowed key
            // so to test only one invalid key is enough
            ['non_existing_key' => 'value']
        );
        // Bad Request (400) means that the client sent the request wrongly; it's a client error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Request body malformed.');

        // Handle request after defining expected exceptions
        $this->app->handle($request);
    }
}