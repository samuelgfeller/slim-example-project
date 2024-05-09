<?php

namespace App\Test\Integration\Client;

use App\Domain\Client\Data\ClientListResultCollection;
use App\Domain\User\Data\UserData;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Service\UserNameAbbreviator;
use App\Infrastructure\Utility\Hydrator;
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
 * - client list page action
 *   - authorization
 *   - unauthenticated
 * - client list with filter json request
 * - client list with invalid filters.
 */
class ClientListActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Normal page action while having an active session.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return void
     */
    public function testClientListPageActionAuthorization(): void
    {
        // Insert logged-in user with the lowest privilege
        $userRow = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::NEWCOMER]),
        );
        // Insert another user and status to fully load the different filter chip options
        $this->insertFixture(UserFixture::class);
        $this->insertFixture(ClientStatusFixture::class);

        $request = $this->createRequest('GET', $this->urlFor('client-list-page'));
        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);

        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    /**
     * Test client list page load without active session.
     *
     * @return void
     */
    public function testClientListPageActionUnauthenticated(): void
    {
        // Request route to client read page while not being logged in
        $requestRoute = $this->urlFor('client-list-page');
        $request = $this->createRequest('GET', $requestRoute);
        $response = $this->app->handle($request);
        // Assert 302 Found redirect to log in url
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $requestRoute]);
        self::assertSame($expectedLoginUrl, $response->getHeaderLine('Location'));
    }

    /**
     * Test list of clients with different kinds of filters.
     *
     * @param array $filterQueryParamsArr
     * @param string $expectedClientsWhereString
     * @param array $authenticatedUserAttributes
     * @param array $clientsToInsert
     * @param array $usersToInsert
     * @param array $clientStatusesToInsert
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\Client\ClientListProvider::class, 'clientListFilterCases')]
    public function testClientListWithFilterAction(
        array $filterQueryParamsArr,
        string $expectedClientsWhereString,
        array $authenticatedUserAttributes,
        array $clientsToInsert,
        array $usersToInsert,
        array $clientStatusesToInsert,
    ): void {
        $users = $this->insertFixture(UserFixture::class, $usersToInsert);
        $statuses = $this->insertFixture(ClientStatusFixture::class, $clientStatusesToInsert);
        // Insert authenticated user before client
        $loggedInUserId = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId($authenticatedUserAttributes),
        )['id'];
        // Insert clients
        $clients = $this->insertFixture(ClientFixture::class, $clientsToInsert);
        // Add session
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('client-list', [], $filterQueryParamsArr)
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Filter fixture records with given row filter params
        $clientRows = $this->findTableRowsWhere(
            'client',
            $expectedClientsWhereString,
            null,
            'left join user on user.id = client.user_id'
        );

        // Create expected array based on fixture records
        $expected['clients'] = [];
        foreach ($clientRows as $clientRow) {
            // Add clients to expected array
            $expected['clients'][] = [
                // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
                'id' => $clientRow['id'],
                'firstName' => $clientRow['first_name'],
                'lastName' => $clientRow['last_name'],
                'birthdate' => $clientRow['birthdate'],
                'location' => $clientRow['location'],
                'phone' => $clientRow['phone'],
                'sex' => $clientRow['sex'],
                'vigilanceLevel' => $clientRow['vigilance_level'],
                'userId' => $clientRow['user_id'],
                'clientStatusId' => $clientRow['client_status_id'],
                'age' => (new \DateTime())->diff(new \DateTime($clientRow['birthdate']))->y,
                // Below not asserted as this test is about filtering not authorization
                // 'personalInfoPrivilege' => null
                // 'clientStatusPrivilege' => 'NONE'
                // 'assignedUserPrivilege' => 'NONE'
                // 'noteCreationPrivilege' => null
            ];
        }
        $clientStatuses = $this->findTableRowsWhere('client_status', 'deleted_at IS NULL');
        foreach ($clientStatuses as $clientStatus) {
            $expected['statuses'][$clientStatus['id']] = $clientStatus['name'];
        }
        $allUsers = $this->findTableRowsWhere('user', 'deleted_at IS NULL');
        // Username abbreviator returns users in array with as key the user id and name the abbreviated name
        $expected['users'] = $this->container->get(UserNameAbbreviator::class)->abbreviateUserNames(
            $this->container->get(Hydrator::class)->hydrate($allUsers, UserData::class)
        );
        $expected['sexes'] = (new ClientListResultCollection())->sexes;

        // Get response json data
        $responseJson = $this->getJsonData($response);
        // Remove keys from response json that are not in the clients expected array
        foreach ($responseJson['clients'] as $key => $clientFromResponse) {
            // Replace client from response array with the same values but removed keys that are not in the expected array
            $responseJson['clients'][$key] = array_intersect_key($clientFromResponse, $expected['clients'][0]);
        }
        self::assertSame($expected, $responseJson);
    }

    /**
     * Request list of clients but with invalid filter.
     *
     * @param array $filterQueryParamsArr Filter as GET paramets
     * @param array $expectedBody Expected response body
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\Client\ClientListProvider::class, 'clientListInvalidFilterCases')]
    public function testClientListActionInvalidFilters(array $filterQueryParamsArr, array $expectedBody): void
    {
        $loggedInUserId = $this->insertFixture(UserFixture::class)['id'];
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('client-list', [], $filterQueryParamsArr)
        );

        $response = $this->app->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $this->assertJsonData($expectedBody, $response);
    }

    public function testSaveFilterChipsClientList(): void
    {
        $loggedInUserId = $this->insertFixture(UserFixture::class)['id'];
        // Insert client assigned to authenticated user
        $clientRow = $this->insertFixture(ClientFixture::class, ['user_id' => $loggedInUserId]);

        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $filterQueryParamsArr = [
            'user' => (string)$loggedInUserId,
            'filterIds[]' => 'assigned_to_me',
            'saveFilter' => '1',
        ];

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('client-list', [], $filterQueryParamsArr)
        );

        $response = $this->app->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Check if the client was returned with the client id (the entire client list response data is already tested)
        $this->assertPartialJsonData(
            [0 => ['id' => $clientRow['id']]],
            $this->getJsonData($response)['clients']
        );

        // Check if the filter was saved in the database
        $filterSettingRow = $this->findTableRowsWhere(
            'user_filter_setting',
            "user_id = $loggedInUserId"
        )[0];
        self::assertEquals(
            ['user_id' => $loggedInUserId, 'filter_id' => 'assigned_to_me', 'module' => 'client-list'],
            $filterSettingRow
        );
    }
}
