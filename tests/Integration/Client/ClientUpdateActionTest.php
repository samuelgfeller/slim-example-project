<?php

namespace App\Test\Integration\Client;

use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Enum\UserRole;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use App\Test\Trait\AuthorizationTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpJsonTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

/**
 * Client update integration test:
 * - normal update with different user roles
 * - invalid update requests
 * - unauthenticated client update
 * - client update request with value to change being the same as in database
 */
class ClientUpdateActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Test client values update when authenticated with different user roles.
     *
     * @param array $userLinkedToClientRow client owner attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $requestData array of data for the request body
     * @param array $expectedResult HTTP status code, bool if db_entry_created and json_response
     *
     * @throws \JsonException|ContainerExceptionInterface|NotFoundExceptionInterface
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\Client\ClientUpdateProvider::class, 'clientUpdateAuthorizationCases')]
    public function testClientSubmitUpdateActionAuthorization(
        array $userLinkedToClientRow,
        array $authenticatedUserRow,
        array $requestData,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixtures($authenticatedUserRow, $userLinkedToClientRow);

        // Insert client status
        $clientStatusId = $this->insertFixture(ClientStatusFixture::class)['id'];
        // Insert client that will be used for this test
        $clientAttributes = ['client_status_id' => $clientStatusId, 'user_id' => $userLinkedToClientRow['id']];
        // If deleted at is provided in the request data, it means that client should be undeleted
        if (array_key_exists('deleted_at', $requestData)) {
            // Add deleted at to client attributes
            $clientAttributes = array_merge($clientAttributes, ['deleted_at' => date('Y-m-d H:i:s')]);
        }
        $clientRow = $this->insertFixture(ClientFixture::class, $clientAttributes);

        // Insert other user and client status used for the modification request if needed.
        if (isset($requestData['user_id'])) {
            // Replace the value "new" from the data to be changed array with a new,
            // different user id (user linked previously + 1)
            $requestData['user_id'] = $clientRow['user_id'] + 1;
            $this->insertFixture(UserFixture::class, ['id' => $requestData['user_id']]);
        }
        if (isset($requestData['client_status_id'])) {
            // Add previously not existing client status to request data (previous client status + 1)
            $requestData['client_status_id'] = $clientRow['client_status_id'] + 1;
            $this->insertFixture(ClientStatusFixture::class, ['id' => $requestData['client_status_id']]);
        }

        // Simulate logged-in user by setting the user_id session variable
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
        if ($expectedResult['dbChanged'] === true) {
            // HTML form element names are the same as the database columns, the same request array can be taken
            // to assert the db
            // Check that changes requested in the request body are reflected in the database
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
            $expectedResult['jsonResponse']['data'] = [
                'age' => (new \DateTime())->diff(new \DateTime($requestData['birthdate']))->y,
            ];
        }

        $this->assertJsonData($expectedResult['jsonResponse'], $response);
    }

    /**
     * Test client validation.
     *
     * @param array $requestBody
     * @param array $jsonResponse
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\Client\ClientUpdateProvider::class, 'invalidClientUpdateProvider')]
    public function testClientSubmitUpdateActionInvalid(array $requestBody, array $jsonResponse): void
    {
        // Insert user that is allowed to change content
        $userId = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::MANAGING_ADVISOR]),
        )['id'];
        $clientStatusId = $this->insertFixture(ClientStatusFixture::class)['id'];
        // Insert client that will be used for this test
        $clientRow = $this->insertFixture(
            ClientFixture::class,
            ['client_status_id' => $clientStatusId, 'user_id' => $userId],
        );

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('client-update-submit', ['client_id' => '1']),
            $requestBody
        );

        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

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
        $requestRoute = $this->urlFor('client-update-submit', ['client_id' => '1']);
        // Request body not important as it shouldn't be taken into account when unauthenticated
        $request = $this->createJsonRequest('PUT', $requestRoute, ['user_id' => 2]);

        // Make request
        $response = $this->app->handle($request);
        // Assert 302 Found redirect to login url
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page');
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }

    /**
     * Test that if user makes update request but the content is the same
     * as what's in the database, the response contains the warning.
     *
     *@throws ContainerExceptionInterface|NotFoundExceptionInterface
     *
     * @return void
     */
    public function testClientSubmitUpdateActionUnchangedContent(): void
    {
        // Insert user that is allowed to change content
        $userId = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::MANAGING_ADVISOR]),
        )['id'];
        $clientStatusId = $this->insertFixture(ClientStatusFixture::class)['id'];
        // Insert client that will be used for this test
        $clientRow = $this->insertFixture(
            ClientFixture::class,
            ['client_status_id' => $clientStatusId, 'user_id' => $userId],
        );

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('client-update-submit', ['client_id' => '1']),
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
}
