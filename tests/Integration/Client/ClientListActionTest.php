<?php

namespace App\Test\Integration\Client;

use App\Common\Hydrator;
use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\Note\Data\NoteData;
use App\Domain\User\Data\UserData;
use App\Domain\User\Service\UserNameAbbreviator;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\RouteTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;

/**
 * Copied and pasted content from client for now
 */

/**
 * - client list page action
 *   - Unauthenticated
 *   - Authenticated
 * - client list json request
 * - client list filtered
 * - client list with invalid filters
 */
class ClientListActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTrait;


    /**
     * Normal page action while having an active session.
     *
     * @return void
     */
    public function testClientListPageAction_authenticated(): void
    {
        // Insert logged-in user
        $userRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $userRow);

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
    public function testClientListPageAction_unauthenticated(): void
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
     * Request list of all clients
     * Fixtures dependency:
     *      UserFixture: one user (for session), all users linked to any clients in client fixture
     *      ClientStatusFixture: all clients statuses linked to any client
     *      ClientFixture: at least one active client and one deleted
     *
     * @return void
     */
    public function testClientListClientLoadAction(): void
    {
        // Insert users and statuts linked to clients
        $this->insertFixtures([UserFixture::class, ClientStatusFixture::class]);

        // Insert all clients including deleted one
        $this->insertFixtureWhere(['deleted_at' => null], ClientFixture::class);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('client-list')
        );

        // Simulate logged-in user with role user
        $loggedInUserId = $this->findRecordsFromFixtureWhere(['role' => 'user'], UserFixture::class)[0]['id'];
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Find all but deleted clients as they are expected to returned by the server
        $clientRows = $this->findRecordsFromFixtureWhere(['deleted_at' => null], ClientFixture::class);
        // Create expected array based on fixture records
        $expected = [];
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
                'userId' => $clientRow['user_id'],
                'clientStatusId' => $clientRow['client_status_id'],
                'updatedAt' => $clientRow['updated_at'],
                'createdAt' => $clientRow['created_at'],
                'age' => (new \DateTime())->diff(new \DateTime($clientRow['birthdate']))->y,
                'notes' => null,
                'notesAmount' => null,
                'mainNoteData' => (array)new NoteData(), // Empty on client list but perhaps added later to display on hover
            ];
        }
        $clientStatuses = $this->findRecordsFromFixtureWhere(['deleted_at' => null], ClientStatusFixture::class);
        foreach ($clientStatuses as $clientStatus) {
            $expected['statuses'][$clientStatus['id']] = $clientStatus['name'];
        }
        $allUsers = $this->findRecordsFromFixtureWhere(['deleted_at' => null], UserFixture::class);
        $allUsersAsObjects = $this->container->get(Hydrator::class)->hydrate($allUsers, UserData::class);
        // Username abbreviator returns users in array with as key the user id and name the abbreviated name
        $expected['users'] = $this->container->get(UserNameAbbreviator::class)->abbreviateUserNamesForDropdown(
            $allUsersAsObjects
        );
        $expected['sexes'] = (new ClientResultDataCollection())->sexes;

        $this->assertJsonData($expected, $response);
    }

    /**
     * Request list of clients matching given filters
     * Fixtures dependency:
     *      UserFixture: one user with id 1 (for session)(better if at least two)
     *      ClientFixture: one client (better if at least two)
     *
     * @dataProvider \App\Test\Provider\Client\ClientFilterCaseProvider::provideValidFilter()
     *
     * @param array $queryParams Filter as GET paramets
     * @param array<string, mixed> $recordFilter Filter as record filter like ['col' => 'value']
     * @return void
     */
    public function testClientListAction_withFilters(array $queryParams, array $recordFilter): void
    {
        // All user fixtures required to insert all client fixtures
        $this->insertFixtures([UserFixture::class, ClientFixture::class]);

        $request = $this->createRequest(
            'GET',
            $this->urlFor('client-list', [], $queryParams)
        ) // Needed until Nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set [SLE-105]
        ->withQueryParams($queryParams);

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Create expected array based on fixture records
        $expected = [];
        // Get clients records matching filter
        $clientRecords = $this->findRecordsFromFixtureWhere($recordFilter, ClientFixture::class);
        foreach ($clientRecords as $clientRow) {
            // Linked user record
            $userRow = $this->findRecordsFromFixtureWhere(['id' => $clientRow['user_id']], UserFixture::class)[0];
            // Build expected array
            $expected[] = [
                // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
                'clientId' => $clientRow['id'],
                'userId' => $userRow['id'],
                'clientMessage' => $clientRow['message'],
                'clientCreatedAt' => $this->changeDateFormat($clientRow['created_at']),
                'clientUpdatedAt' => $this->changeDateFormat($clientRow['updated_at']),
                'userFullName' => $userRow['first_name'] . ' ' . $userRow['surname'],
                'userRole' => $userRow['role'],
                'userMutationRights' => UserNoteData::MUTATION_PERMISSION_NONE, // None as not logged in
            ];
        }

        $this->assertJsonData($expected, $response);
    }

    /**
     * Request list of clients but with invalid filter
     *
     * @dataProvider \App\Test\Provider\Client\ClientFilterCaseProvider::provideInvalidFilter()
     *
     * @param array $queryParams Filter as GET paramets
     * @param array $expectedBody Expected response body
     * @return void
     */
    public function testClientListAction_invalidFilters(array $queryParams, array $expectedBody): void
    {
        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('client-list', [], $queryParams)
        ) // Needed until Nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set
        ->withQueryParams($queryParams);

        $response = $this->app->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $this->assertJsonData($expectedBody, $response);
    }

    /**
     * Request list of all clients when admin is logged in
     * Expected is that all clients have all permissions
     *
     * Fixtures dependency:
     *      UserFixture: one user with id 1 (for session)(better if at least two)
     *      ClientFixture: one client (better if at least two)
     *
     * @return void
     */
    public function testClientListAction_asAdmin(): void
    {
        // All user fixtures required to insert all client fixtures
        $this->insertFixtures([UserFixture::class, ClientFixture::class]);

        // Logged in user 1 role 'admin'
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('client-list')
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that mutation permission is all on all clients
        foreach ($this->getJsonData($response) as $client) {
            self::assertSame(UserNoteData::MUTATION_PERMISSION_ALL, $client['userMutationRights']);
        }
    }

    /**
     * ClientFinder changes the date into the default format in Europe
     *
     * @param string|null $date
     * @return string|null
     */
    private function changeDateFormat(?string $date): ?string
    {
        return $date ? date('d.m.Y H:i:s', strtotime($date)) : null;
    }
}