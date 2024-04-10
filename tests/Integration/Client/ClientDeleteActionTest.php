<?php

namespace App\Test\Integration\Client;

use App\Domain\User\Enum\UserActivity;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
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
 * Client submit delete action tests
 *  - Authenticated with different user roles
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
     * Test delete client submit with different authenticated user roles.
     *
     * @param array $userLinkedToClientRow client owner attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, bool if db is supposed to change and json_response
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\Client\ClientDeleteProvider::class, 'clientDeleteUsersAndExpectedResultProvider')]
    public function testClientSubmitDeleteActionAuthorization(
        array $userLinkedToClientRow,
        array $authenticatedUserRow,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixturesWithAttributes($authenticatedUserRow, $userLinkedToClientRow);

        // Insert client status
        $clientStatusId = $this->insertFixture(new ClientStatusFixture())['id'];
        // Insert client linked to given user
        $clientRow = $this->insertFixture(
            new ClientFixture(),
            ['client_status_id' => $clientStatusId, 'user_id' => $userLinkedToClientRow['id']],
        );

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
            self::assertNotNull($this->getTableRowById('client', $clientRow['id'], ['deleted_at']));
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
     * Test that when user is not logged in 401 Unauthorized is returned.
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

    // Unchanged content test not done as it's not being used by the frontend
}
