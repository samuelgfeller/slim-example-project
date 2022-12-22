<?php

namespace App\Test\Integration\Client;

use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Enum\UserRole;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\AuthorizationTestTrait;
use App\Test\Traits\DatabaseExtensionTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;
use Slim\Exception\HttpBadRequestException;

/**
 * Client update integration test:
 * - normal update
 * - invalid note update
 * - unauthenticated client update
 * - client update request with value to change being the same as in database
 * NOT in this test:
 * - edit non-existing client - reason: delete request on non-existing client is tested.
 */
class ClientUpdateActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Test client values update when authenticated with different user roles.
     *
     * @dataProvider \App\Test\Provider\Client\ClientUpdateProvider::clientUpdateUsersAndExpectedResultProvider()
     *
     * @param array $userLinkedToClientRow client owner attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $requestData array of data for the request body
     * @param array $expectedResult HTTP status code, bool if db_entry_created and json_response
     *
     * @return void
     */
    public function testClientSubmitUpdateActionAuthorization(
        array $userLinkedToClientRow,
        array $authenticatedUserRow,
        array $requestData,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixturesWithAttributes($userLinkedToClientRow, $authenticatedUserRow);

        // Insert client status
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
        // Insert client that will be used for this test
        $clientAttributes = ['client_status_id' => $clientStatusId, 'user_id' => $userLinkedToClientRow['id']];
        // If deleted at is provided in the request data, it means that client should be undeleted
        if (array_key_exists('deleted_at', $requestData)) {
            // Add deleted at to client attributes
            $clientAttributes = array_merge($clientAttributes, ['deleted_at' => date('Y-m-d H:i:s')]);
        }
        $clientRow = $this->insertFixturesWithAttributes(
            $clientAttributes,
            ClientFixture::class
        );

        // Insert other user and client status that are used for the modification request if needed
        if (isset($requestData['user_id'])) {
            // Add 1 to user_id linked to client
            $requestData['user_id'] = $clientRow['user_id'] + 1;
            $this->insertFixturesWithAttributes(['id' => $requestData['user_id']], UserFixture::class);
        }
        if (isset($requestData['client_status_id'])) {
            // Add 1 to client status id
            $requestData['client_status_id'] = $clientRow['client_status_id'] + 1;
            $this->insertFixturesWithAttributes(['id' => $requestData['client_status_id']], ClientStatusFixture::class);
        }

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('client-update-submit', ['client_id' => $clientRow['id']]),
            $requestData
        );

        $response = $this->app->handle($request);
        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // Assert database
        if ($expectedResult['db_changed'] === true) {
            // HTML form element names are the same as the database columns, the same request array can be taken to assert the db
            // Check that data in request body was changed
            $this->assertTableRowEquals($requestData, 'client', $clientRow['id']);
            // Get client row from db to have the assigned_at timestamp to assert user activity entry json data
            $clientRow = $this->findTableRowById('client', $clientRow['id']);
            if ($clientRow['assigned_at'] !== null) {
                $updateActivityData = array_merge($requestData, ['assigned_at' => $clientRow['assigned_at']]);
            }
            // Assert that user activity is inserted
            $this->assertTableRow(
                [
                    'action' => UserActivity::UPDATED->value,
                    'table' => 'client',
                    'row_id' => $clientRow['id'],
                    'data' => json_encode(
                        // Add the missing "assigned_at" that is added while updating the client with a current timestamp
                        $updateActivityData ?? $requestData,
                        JSON_THROW_ON_ERROR
                    ),
                ],
                'user_activity',
                (int)$this->findLastInsertedTableRow('user_activity')['id'],
            );
        } else {
            // If db is not expected to change, data should remain the same as when it was inserted from the fixture
            $this->assertTableRowEquals($clientRow, 'client', $clientRow['id']);
            // Assert that user activity is inserted but with status failed
            $this->assertTableRow(
                [
                    'action' => UserActivity::UPDATED->value,
                    'table' => 'client',
                    'row_id' => $clientRow['id'],
                    'data' => json_encode(array_merge(['status' => 'FAILED'], $requestData), JSON_THROW_ON_ERROR),
                ],
                'user_activity',
                (int)$this->findLastInsertedTableRow('user_activity')['id']
            );
        }

        // If birthdate is in request body, age is returned in response data
        if (array_key_exists('birthdate', $requestData)) {
            $expectedResult['json_response']['data'] = [
                'age' => (new \DateTime())->diff(new \DateTime($requestData['birthdate']))->y,
            ];
        }

        $this->assertJsonData($expectedResult['json_response'], $response);
    }

    /**
     * Test client values validation.
     *
     * @dataProvider \App\Test\Provider\Client\ClientUpdateProvider::invalidClientUpdateValuesAndExpectedResponseProvider()
     *
     * @param array $requestBody
     * @param array $jsonResponse
     *
     * @return void
     */
    public function testClientSubmitUpdateActionInvalid(array $requestBody, array $jsonResponse): void
    {
        // Insert user that is allowed to change content
        $userId = $this->insertFixturesWithAttributes(
            $this->addUserRoleId(['user_role_id' => UserRole::MANAGING_ADVISOR]),
            UserFixture::class
        )['id'];
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
        // Insert client that will be used for this test
        $clientRow = $this->insertFixturesWithAttributes(
            ['client_status_id' => $clientStatusId, 'user_id' => $userId],
            ClientFixture::class
        );

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('client-update-submit', ['client_id' => 1]),
            $requestBody
        );

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $clientRow['user_id']);

        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // database should be unchanged
        $this->assertTableRowEquals($clientRow, 'client', $clientRow['id']);

        $this->assertJsonData($jsonResponse, $response);
    }

    /**
     * Test that dropdown values client status and assigned user
     * can be changed when authenticated. Any user role can do this.
     *
     * @return void
     */
    public function testClientSubmitUpdateActionUnauthenticated(): void
    {
        // Request route to client read page while not being logged in
        $requestRoute = $this->urlFor('client-update-submit', ['client_id' => 1]);
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
    public function testClientSubmitUpdateActionUnchangedContent(): void
    {
        // Insert user that is allowed to change content
        $userId = $this->insertFixturesWithAttributes(
            $this->addUserRoleId(['user_role_id' => UserRole::MANAGING_ADVISOR]),
            UserFixture::class
        )['id'];
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
        // Insert client that will be used for this test
        $clientRow = $this->insertFixturesWithAttributes(
            ['client_status_id' => $clientStatusId, 'user_id' => $userId],
            ClientFixture::class
        );

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $clientRow['user_id']);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('client-update-submit', ['client_id' => 1]),
            // Submitted first name is EXACTLY THE SAME as what's already in the database
            ['first_name' => $clientRow['first_name']]
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that response contains warning
        $this->assertJsonData(
            ['status' => 'warning', 'message' => 'The client was not updated.', 'data' => null],
            $response
        );

        $this->assertTableRowEquals(['first_name' => $clientRow['first_name']], 'client', $clientRow['id']);
    }

    /**
     * Test client modification with malformed request body.
     *
     * @return void
     */
    public function testClientSubmitUpdateActionMalformedRequest(): void
    {
        // Action class should directly return error so only logged-in user has to be inserted
        $userData = $this->insertFixturesWithAttributes([], UserFixture::class);

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userData['id']);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('client-update-submit', ['client_id' => 1]),
            // The update request can format the request body pretty freely as long as it doesn't contain a non-allowed key
            // so to test only one invalid key is enough
            ['non_existing_key' => 'value']
        );
        // 400 Bad Request means that the client sent the request wrongly; it's a client error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Request body malformed.');

        // Handle request after defining expected exceptions
        $this->app->handle($request);
    }
}
