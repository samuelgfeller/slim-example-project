<?php

namespace App\Test\TestCase\Client\Delete;

use App\Module\User\Enum\UserActivity;
use App\Module\User\Enum\UserRole;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\NoteFixture;
use App\Test\Fixture\UserFixture;
use App\Test\TestCase\Client\Delete;
use App\Test\Trait\AppTestTrait;
use App\Test\Trait\AuthorizationTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpJsonTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

/**
 * Submit client delete request tests
 *  - Authenticated delete request with different user roles
 *  - Authenticated undelete request with different user roles
 *  - Unauthenticated.
 */
class ClientDeleteActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Test request to delete client with different authenticated user roles.
     *
     * @param array $userLinkedToClientRow client owner attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, bool if db is supposed to change and json_response
     *
     * @return void
     */
    #[DataProviderExternal(Delete\ClientDeleteProvider::class, 'clientDeleteProvider')]
    public function testClientSubmitDeleteActionAuthorization(
        array $userLinkedToClientRow,
        array $authenticatedUserRow,
        array $expectedResult,
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixtures($authenticatedUserRow, $userLinkedToClientRow);

        // Insert client status
        $clientStatusId = $this->insertFixture(ClientStatusFixture::class)['id'];
        // Insert client linked to given user
        $clientRow = $this->insertFixture(
            ClientFixture::class,
            ['client_status_id' => $clientStatusId, 'user_id' => $userLinkedToClientRow['id']],
        );

        // Insert note linked to client
        $noteRow = $this->insertFixture(NoteFixture::class, ['client_id' => $clientRow['id']]);

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $request = $this->createJsonRequest(
            'DELETE',
            // Construct url /clients/1 with urlFor()
            $this->urlFor('client-delete-submit', ['client_id' => $clientRow['id']]),
        );

        $response = $this->app->handle($request);

        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // Assert database
        if ($expectedResult['dbChanged'] === true) {
            // Assert that deleted_at is NOT null
            self::assertNotNull($this->getTableRowById('client', $clientRow['id'], ['deleted_at'])['deleted_at']);
            // Assert that note is deleted
            self::assertNotNull($this->getTableRowById('note', $noteRow['id'], ['deleted_at'])['deleted_at']);
            // Assert that user activity is inserted
            $this->assertTableRow(
                [
                    'action' => UserActivity::DELETED->value,
                    'table' => 'client',
                    'row_id' => $clientRow['id'],
                    'data' => null,
                ],
                'user_activity',
                (int)$this->findLastInsertedTableRow('user_activity')['id']
            );
        } else {
            // If db is not expected to change deleted at has to be null
            $this->assertTableRow(['deleted_at' => null], 'client', $clientRow['id']);
            $this->assertTableRowCount(0, 'user_activity');
        }

        // Assert response json content
        $this->assertJsonData($expectedResult['jsonResponse'], $response);
    }

    /**
     * Test request to undelete client with different authenticated user roles.
     *
     * @return void
     */
    #[DataProviderExternal(Delete\ClientDeleteProvider::class, 'clientUndeleteDeleteProvider')]
    public function testClientSubmitUndeleteActionAuthorization(
        array $userLinkedToClientRow,
        array $authenticatedUserRow,
        array $requestData,
        array $expectedResult,
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixtures($authenticatedUserRow, $userLinkedToClientRow);

        // Insert client status
        $clientStatusId = $this->insertFixture(ClientStatusFixture::class)['id'];
        // Insert deleted client that will be used for this test
        $clientAttributes = [
            'client_status_id' => $clientStatusId,
            'user_id' => $userLinkedToClientRow['id'],
            // Add deleted_at
            'deleted_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        $clientRow = $this->insertFixture(ClientFixture::class, $clientAttributes);

        // Insert note that was deleted before the client deletion (should not be restored)
        $noteDeletedBeforeClientRow = $this->insertFixture(
            NoteFixture::class,
            [
                'client_id' => $clientRow['id'],
                // Note deleted 1 day before the client was deleted
                'deleted_at' => (new \DateTime($clientRow['deleted_at']))
                    ->sub(new \DateInterval('P1D'))->format('Y-m-d H:i:s'),
            ]
        );

        // Insert note linked to client with the same time as client deletion (should be restored with client)
        $noteDeletedWithClientRow = $this->insertFixture(
            NoteFixture::class,
            [
                'client_id' => $clientRow['id'],
                // Deleted within 2s of client deletion (1s after to test buffer)
                'deleted_at' => (new \DateTime($clientRow['deleted_at']))->modify('-2 second')->format('Y-m-d H:i:s'),
            ]
        );

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
            // Check that deleted_at is null
            $this->assertTableRowEquals(['deleted_at' => null], 'client', $clientRow['id']);

            // Check that note deleted before client deletion is not restored
            $this->assertTableRowEquals(
                ['deleted_at' => $noteDeletedBeforeClientRow['deleted_at']],
                'note',
                $noteDeletedBeforeClientRow['id']
            );

            // Check that note deleted with client is restored
            $this->assertTableRowEquals(['deleted_at' => null], 'note', $noteDeletedWithClientRow['id']);

            // Assert that user activity is inserted
            $this->assertTableRow(
                [
                    'action' => UserActivity::UPDATED->value,
                    'table' => 'client',
                    'row_id' => $clientRow['id'],
                    'data' => json_encode($requestData, JSON_PARTIAL_OUTPUT_ON_ERROR),
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

        $this->assertJsonData($expectedResult['jsonResponse'], $response);
    }

    /**
     * Delete request from authorized user but the client does not exist.
     *
     * @return void
     */
    public function testClientSubmitDeleteError(): void
    {
        // Insert authenticated authorized user
        $userRow = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::ADMIN])
        );

        // Not inserting client

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);

        $request = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('client-delete-submit', ['client_id' => '1']),
        );

        $response = $this->app->handle($request);

        // Assert response HTTP status code: 200
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert response json content
        $this->assertJsonData(['status' => 'warning', 'message' => 'Client has not been deleted.'], $response);
    }

    /**
     * Test that when a user is not logged in 401 Unauthorized is returned.
     *
     * @return void
     */
    public function testClientSubmitDeleteActionUnauthenticated(): void
    {
        $request = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('client-delete-submit', ['client_id' => '1']),
        );

        $response = $this->app->handle($request);

        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page');

        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }
}
