<?php

namespace App\Test\Integration\Client;

use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Enum\UserRole;
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
 * Client creation submit tests
 *  - Normal client creation
 *  - With invalid values -> 422
 *  - With malformed request body -> Bad request exception.
 */
class ClientCreateActionTest extends TestCase
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
     * Client creation with valid data.
     *
     * @dataProvider \App\Test\Provider\Client\ClientCreateProvider::clientCreationUsersAndExpectedResultProvider()
     *
     * @param array $userLinkedToClientRow client owner attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, bool if db_entry_created and json_response
     *
     * @return void
     */
    public function testClientSubmitCreateActionAuthorization(
        array $userLinkedToClientRow,
        array $authenticatedUserRow,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixturesWithAttributes($userLinkedToClientRow, $authenticatedUserRow);

        // Client status is not authorization relevant for client creation
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];

        $clientCreationValues = [
            'first_name' => 'New',
            'last_name' => 'Client',
            'birthdate' => '2000-03-15',
            'location' => 'Basel',
            'phone' => '+41 77 222 22 22',
            'email' => 'new-user@email.com',
            'sex' => 'M',
            'client_message' => null,
            'vigilance_level' => null,
            'user_id' => $userLinkedToClientRow['id'],
            'client_status_id' => $clientStatusId,
            'message' => 'Test main note.',
        ];

        // Simulate session
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
        // Make request
        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('client-create-submit'),
            $clientCreationValues
        );
        $response = $this->app->handle($request);
        // Assert response status code: 201 Created
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // If db record is expected to be created assert that
        if ($expectedResult['db_entry_created'] === true) {
            // Remove main note from client creation values as message is stored in different table
            $noteValues['message'] = $clientCreationValues['message'];
            unset($clientCreationValues['message']);
            $clientDbRow = $this->findLastInsertedTableRow('client');
            // Assert that db entry corresponds to the given client creation values. This is possible with
            // $clientCreationValues as the keys that the frontend sends to the server are the same as database columns.
            // It is done with the function assertTableRow even though we already have the clientDbRow for simplicity
            $this->assertTableRowEquals($clientCreationValues, 'client', $clientDbRow['id']);
            // The same check could also be done with array_intersect_key (which removes any keys from the db array
            // that are not present in the creation values array) like this
            // self::assertSame($clientCreationValues, array_intersect_key($clientDbRow, $clientCreationValues));

            // Test that main note was created
            $noteId = $this->findLastInsertedTableRow('note')['id'];
            $this->assertTableRowEquals($noteValues, 'note', $noteId);

            // Assert user activity
            // Add client_message to creation values as they are inserted in user_activity
            $this->assertTableRowEquals(
                [
                    'action' => UserActivity::CREATED->value,
                    'table' => 'client',
                    'row_id' => $clientDbRow['id'],
                    'data' => json_encode($clientCreationValues, JSON_THROW_ON_ERROR),
                ],
                'user_activity',
                (int)$this->findTableRowsByColumn('user_activity', 'table', 'client')[0]['id']
            );
            // Note user activity entry
            // Add other note values
            $noteValues['client_id'] = $clientDbRow['id'];
            $noteValues['user_id'] = $userLinkedToClientRow['id'];
            $noteValues['is_main'] = 1;
            $this->assertTableRow(
                [
                    'action' => UserActivity::CREATED->value,
                    'table' => 'note',
                    'row_id' => $noteId,
                    'data' => json_encode($noteValues, JSON_THROW_ON_ERROR),
                ],
                'user_activity',
                (int)$this->findTableRowsByColumn('user_activity', 'table', 'note')[0]['id']
            );
        } else {
            // 0 rows expected in client table
            $this->assertTableRowCount(0, 'client');
            $this->assertTableRowCount(0, 'user_activity');
        }

        $this->assertJsonData($expectedResult['json_response'], $response);
    }

    /**
     * Test client creation values validation.
     *
     * @dataProvider \App\Test\Provider\Client\ClientCreateProvider::invalidClientCreationValuesAndExpectedResponseProvider()
     *
     * @param array $requestBody
     * @param array $jsonResponse
     *
     * @return void
     */
    public function testClientSubmitCreateActionInvalid(array $requestBody, array $jsonResponse): void
    {
        // Insert managing advisor user which is allowed to create clients
        $userId = $this->insertFixturesWithAttributes(
            $this->addUserRoleId(['user_role_id' => UserRole::MANAGING_ADVISOR]),
            UserFixture::class
        )['id'];
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
        // To test note message validation when submitted in client creation form the client values have to be valid
        if ($requestBody['user_id'] === 'valid' && $requestBody['client_status_id'] === 'valid') {
            $requestBody['user_id'] = $userId;
            $requestBody['client_status_id'] = $clientStatusId;
        }

        // Simulate session
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('client-create-submit'),
            $requestBody
        );

        $response = $this->app->handle($request);
        // Assert 422
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // No client should have been created
        $this->assertTableRowCount(0, 'client');

        $this->assertJsonData($jsonResponse, $response);
    }

    /**
     * Client creation with valid data.
     *
     * @return void
     */
    public function testClientSubmitCreateActionUnauthenticated(): void
    {
        // Create request (no body needed as it shouldn't be interpreted anyway)
        $request = $this->createJsonRequest('POST', $this->urlFor('client-create-submit'), []);
        // Provide redirect to if unauthorized header to test if UserAuthenticationMiddleware returns correct login url
        $redirectAfterLoginRouteName = 'client-list-page';
        $request = $request->withAddedHeader('Redirect-to-route-name-if-unauthorized', $redirectAfterLoginRouteName);
        // Make request
        $response = $this->app->handle($request);
        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor(
            'login-page',
            [],
            ['redirect' => $this->urlFor($redirectAfterLoginRouteName)]
        );
        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }

    /**
     * Test client creation with malformed request body.
     *
     * @dataProvider \App\Test\Provider\Client\ClientCreateProvider::malformedRequestBodyCases()
     *
     * @param array $requestBody
     *
     * @return void
     */
    public function testClientSubmitCreateActionMalformedRequestBody(array $requestBody): void
    {
        // Action class should directly return error so only logged-in user has to be inserted
        $userData = $this->insertFixturesWithAttributes([], UserFixture::class);

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userData['id']);

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('client-create-submit'),
            $requestBody
        );

        // Bad Request (400) means that the client sent the request wrongly; it's a frontend error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Request body malformed.');

        // Handle request after defining expected exceptions
        $this->app->handle($request);
    }
}
