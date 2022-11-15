<?php

namespace App\Test\Integration\Client;

use App\Common\Hydrator;
use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\Note\Data\NoteData;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserNameAbbreviator;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\FixtureTrait;
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
        // Insert logged-in user with lowest privilege
        $userRow = $this->insertFixturesWithAttributes(['user_role_id' => 4], UserFixture::class);

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
     * Test list of clients
     *
     * @dataProvider \App\Test\Provider\Client\ClientListCaseProvider::provideValidClientListFilters()
     *
     * @param array<string, mixed> $queryParams
     * @param array<string, mixed> $rowFilter
     * @return void
     */
    public function testClientListAction(array $queryParams, array $rowFilter): void
    {
        // Insert users and status linked to clients and all clients including deleted one
        $this->insertFixtures([UserFixture::class, ClientStatusFixture::class, ClientFixture::class]);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('client-list')
        )   // Needed until Nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set [SLE-105]
        ->withQueryParams($queryParams);

        // Simulate logged-in user with role user
        $loggedInUserId = $this->findRecordsFromFixtureWhere(['role' => 'user'], UserFixture::class)[0]['id'];
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // If user_id is 'session' replace it with the authenticated user id
        if (isset($rowFilter['user_id']) && $rowFilter['user_id'] === 'session'){
            $rowFilter['user_id'] = $loggedInUserId;
        }
        // Filter fixture records with given row filter params
        $clientRows = $this->findRecordsFromFixtureWhere($rowFilter, ClientFixture::class);

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
                'mainNoteData' => (array)new NoteData(),
                // Empty on client list but perhaps added later to display on hover
            ];
        }
        $clientStatuses = $this->findRecordsFromFixtureWhere(['deleted_at' => null], ClientStatusFixture::class);
        foreach ($clientStatuses as $clientStatus) {
            $expected['statuses'][$clientStatus['id']] = $clientStatus['name'];
        }
        $allUsers = $this->findRecordsFromFixtureWhere(['deleted_at' => null], UserFixture::class);
        $allUsersAsObjects = $this->container->get(Hydrator::class)->hydrate($allUsers, UserStatus::class);
        // Username abbreviator returns users in array with as key the user id and name the abbreviated name
        $expected['users'] = $this->container->get(UserNameAbbreviator::class)->abbreviateUserNamesForDropdown(
            $allUsersAsObjects
        );
        $expected['sexes'] = (new ClientResultDataCollection())->sexes;

        $this->assertJsonData($expected, $response);
    }

    /**
     * Request list of clients but with invalid filter
     *
     * @dataProvider \App\Test\Provider\Client\ClientListCaseProvider::provideInvalidClientListFilter()
     *
     * @param array $queryParams Filter as GET paramets
     * @param array $expectedBody Expected response body
     * @return void
     */
    public function testClientListAction_invalidFilters(array $queryParams, array $expectedBody): void
    {
        $this->insertFixture('user', (new UserFixture())->records[0]);
        $this->container->get(SessionInterface::class)->set('user_id', 1);

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