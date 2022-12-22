<?php

namespace App\Test\Integration\Client;

use App\Common\Hydrator;
use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\Note\Data\NoteData;
use App\Domain\User\Data\UserData;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Service\UserNameAbbreviator;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\AuthorizationTestTrait;
use App\Test\Traits\DatabaseExtensionTestTrait;
use App\Test\Traits\FixtureTestTrait;
use App\Test\Traits\HttpJsonExtensionTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * Copied and pasted content from client for now.
 */

/**
 * - client list page action
 *   - Unauthenticated
 *   - Authenticated
 * - client list json request
 * - client list filtered
 * - client list with invalid filters.
 */
class ClientListActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use HttpJsonExtensionTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Normal page action while having an active session.
     *
     * @return void
     */
    public function testClientListPageActionAuthorization(): void
    {
        // Insert logged-in user with the lowest privilege
        $userRow = $this->insertFixturesWithAttributes(
            $this->addUserRoleId(['user_role_id' => UserRole::NEWCOMER]),
            UserFixture::class
        );

        $request = $this->createRequest('GET', $this->urlFor('client-list-page'));
        // Simulate logged-in user with logged-in user id
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
        // Assert 302 Found redirect to login url
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $requestRoute]);
        self::assertSame($expectedLoginUrl, $response->getHeaderLine('Location'));
    }

    /**
     * Test list of clients with different kinds of filters.
     *
     * @dataProvider \App\Test\Provider\Client\ClientListProvider::clientListFilterCases()
     *
     * @param array $filterQueryParamsArr
     * @param string $expectedClientsWhereString
     * @param array $authenticatedUserAttributes
     * @param array $clientsToInsert
     * @param array $usersToInsert
     * @param array $clientStatusesToInsert
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return void
     */
    public function testClientListAction(
        array $filterQueryParamsArr,
        string $expectedClientsWhereString,
        array $authenticatedUserAttributes,
        array $clientsToInsert,
        array $usersToInsert,
        array $clientStatusesToInsert,
    ): void {
        $users = $this->insertFixturesWithAttributes($usersToInsert, UserFixture::class);
        $statuses = $this->insertFixturesWithAttributes($clientStatusesToInsert, ClientStatusFixture::class);
        // Insert authenticated user before client
        $loggedInUserId = $this->insertFixturesWithAttributes(
            $this->addUserRoleId($authenticatedUserAttributes),
            UserFixture::class
        )['id'];
        // Insert clients
        $clients = $this->insertFixturesWithAttributes($clientsToInsert, ClientFixture::class);
        // Add session
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('client-list')
        )   // Needed until Nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set [SLE-105]
        ->withQueryParams($filterQueryParamsArr);

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Filter fixture records with given row filter params
        $clientRows = $this->findTableRowsWhere('client', $expectedClientsWhereString);

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
                'email' => $clientRow['email'],
                'sex' => $clientRow['sex'],
                'clientMessage' => $clientRow['client_message'],
                'vigilanceLevel' => $clientRow['vigilance_level'],
                'userId' => $clientRow['user_id'],
                'clientStatusId' => $clientRow['client_status_id'],
                'updatedAt' => $clientRow['updated_at'],
                'createdAt' => $clientRow['created_at'],
                'age' => (new \DateTime())->diff(new \DateTime($clientRow['birthdate']))->y,
                'notes' => null,
                'notesAmount' => null,
                // Empty on client list but perhaps added later to display on hover
                'mainNoteData' => (new NoteData())->jsonSerialize(),
                // Below not asserted as this test is about filtering not authorization
                // 'mainDataPrivilege' => null
                // 'clientStatusPrivilege' => 'NONE'
                // 'assignedUserPrivilege' => 'NONE'
                // 'noteCreatePrivilege' => null
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
        $expected['sexes'] = (new ClientResultDataCollection())->sexes;

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
     * @dataProvider \App\Test\Provider\Client\ClientListProvider::clientListInvalidFilterCases()
     *
     * @param array $queryParams Filter as GET paramets
     * @param array $expectedBody Expected response body
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return void
     */
    public function testClientListActionInvalidFilters(array $queryParams, array $expectedBody): void
    {
        $loggedInUserId = $this->insertFixturesWithAttributes([], UserFixture::class)['id'];
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('client-list')
        ) // Needed until Nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set
        ->withQueryParams($queryParams);

        $response = $this->app->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $this->assertJsonData($expectedBody, $response);
    }
}
