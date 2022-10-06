<?php


namespace App\Test\Integration\Client;


use App\Domain\User\Data\MutationRights;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\NoteFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\DatabaseExtensionTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use App\Test\Traits\AppTestTrait;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * Client read tests
 *   - Authenticated & unauthenticated page action
 *   - Authenticated & unauthenticated client read note loading
 *   - Authenticated & unauthenticated client read note creation
 *   - Authenticated & unauthenticated client read note update
 *   - Authenticated & unauthenticated client read note deletion
 */
class ClientReadActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use FixtureTrait;

    /**
     * Test that user has to be logged in to display the page
     *
     * @return void
     */
    public function testClientReadPageAction_notLoggedIn(): void
    {
        // Request route to client read page while not being logged in
        $requestRoute = $this->urlFor('client-read-page', ['client_id' => 1]);
        $request = $this->createRequest('GET', $requestRoute);
        $response = $this->app->handle($request);
        // Assert 302 Found redirect to login url
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $requestRoute]);
        self::assertSame($expectedLoginUrl, $response->getHeaderLine('Location'));
    }

    /**
     * Normal page action while being authenticated
     *
     * @return void
     */
    public function testClientReadPageAction_loggedIn(): void
    {
        // Add needed database values to correctly display the page
        // Insert user linked to client and user that is logged in
        $this->insertFixture('user', (new UserFixture())->records[0]);
        // Insert linked status
        $this->insertFixture('client_status', (new ClientStatusFixture())->records[0]);
        // Insert client that should be displayed
        $this->insertFixture('client', (new ClientFixture())->records[0]);

        $request = $this->createRequest('GET', $this->urlFor('client-read-page', ['client_id' => 1]));
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    /**
     * Returns the given $dateTime in the default note format
     *
     * @param string $dateTime
     * @return string
     */
    private function dateTimeToClientReadNoteFormat(string $dateTime): string
    {
        return (new \DateTime($dateTime))->format('d. F Y • H:i');
    }

    /**
     * On client read page display, notes attached to him are
     * loaded with ajax.
     * I'm not sure if this test is better here (use case based)
     * or in NoteListActionTest() with all other filter tests
     *
     * @return void
     */
    public function testClientReadNotesLoad(): void
    {
        // Insert linked users to client and notes (first 2 are needed)
        $nonAdminUserRows = $this->findRecordsFromFixtureWhere(['role' => 'user'], UserFixture::class);
        $this->insertFixtures([UserFixture::class]);
        // Insert linked status (only first one to make dynamic expected array)
        $clientStatusRow = (new ClientStatusFixture())->records[0];
        $this->insertFixture('client_status', $clientStatusRow);
        // Insert client (only first one to make dynamic expected array)
        $clientRow = (new ClientFixture())->records[0];
        $this->insertFixture('client', $clientRow);
        // Insert only linked notes
        $this->insertFixtureWhere(['client_id' => $clientRow['id']], NoteFixture::class);

        $request = $this->createJsonRequest('GET', $this->urlFor('note-list'))->withQueryParams(['client_id' => 1]);
        // Simulate logged-in user with logged-in user id
        $loggedInUserId = $nonAdminUserRows[0]['id'];
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Removing first note from $noteRows as it is a main note that must not be in this response
        $noteRows = $this->findRecordsFromFixtureWhere(
            ['is_main' => 0, 'client_id' => $clientRow['id'], 'deleted_at' => null],
            NoteFixture::class
        );
        // Get logged-in user row to test user rights
        $loggedInUserRow = $this->findRecordsFromFixtureWhere(['id' => $loggedInUserId], UserFixture::class)[0];

        // Determine which mutation rights user has
        $hasMutationRight = static function (string $role, int $ownerId) use ($loggedInUserId): string {
            // Basically same as js function userHasMutationRights() in client-read-template-note.html.js
            return $role === 'admin' || $loggedInUserId === $ownerId
                ? MutationRights::ALL->value : MutationRights::NONE->value;
        };

        $expectedResponseArray = [];

        foreach ($noteRows as $noteRow) {
            // Get linked user record
            $userRow = $this->findRecordsFromFixtureWhere(['id' => $noteRow['user_id']], UserFixture::class)[0];
            $expectedResponseArray[] = [
                // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
                'noteId' => $noteRow['id'],
                'noteMessage' => $noteRow['message'],
                // Same format as in NoteFinder:findAllNotesFromClientExceptMain()
                'noteCreatedAt' => (new \DateTime($noteRow['created_at']))->format('d. F Y • H:i'),
                'noteUpdatedAt' => (new \DateTime($noteRow['updated_at']))->format('d. F Y • H:i'),
                'userId' => $noteRow['user_id'],
                'userFullName' => $userRow['first_name'] . ' ' . $userRow['surname'],
                'userRole' => $userRow['role'],
                // Has to match user rights rules in NoteUserRightSetter.php
                // Currently don't know the best way to implement this dynamically
                'userMutationRights' => $hasMutationRight($loggedInUserRow['role'], $noteRow['user_id']),
            ];
        }

        // Assert response data
        $this->assertJsonData($expectedResponseArray, $response);
    }

    /**
     * Test when note-list request is made from client-read page
     * without being authenticated.
     *
     * @return void
     */
    public function testClientReadNotesLoad_unauthenticated(): void
    {
        $request = $this->createJsonRequest('GET', $this->urlFor('note-list'))
            ->withQueryParams(['client_id' => 1]);

        $redirectToUrlAfterLogin = $this->urlFor('client-read-page', ['client_id' => 1]);
        $request = $request->withAddedHeader('Redirect-to-url-if-unauthorized', $redirectToUrlAfterLogin);

        // Make request
        $response = $this->app->handle($request);

        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor(
            'login-page', [], ['redirect' => $redirectToUrlAfterLogin]
        );
        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }

    /**
     * Test note creation on client-read page while being authenticated.
     *
     * @dataProvider \App\Test\Provider\Client\ClientReadCaseProvider::provideUserWhereConditionForNote()
     * @return void
     */
    public function testClientReadNoteCreation($userLinkedToClientData, $authenticatedUserData, $expectedResult): void
    {
        // To test as "neutral" as possible, a client linked to a non admin user is taken
        // $nonAdminUserRow = $this->findRecordsFromFixtureWhere(['role' => 'user'], UserFixture::class)[0];

        $this->insertFixture('user', $userLinkedToClientData);
        // If authenticated user and user that should be linked to client is different, insert authenticated user
        if ($userLinkedToClientData['id'] !== $authenticatedUserData['id']){
            $this->insertFixture('user', $authenticatedUserData);
        }

        // Insert one client linked to this user
        $clientRow = $this->findRecordsFromFixtureWhere(['user_id' => $userLinkedToClientData['id']], ClientFixture::class)[0];
        // In array first to assert user data later
        $this->insertFixtureWhere(['id' => $clientRow['client_status_id']], ClientStatusFixture::class);
        $this->insertFixture('client', $clientRow);

        // Create request
        $noteMessage = 'Test note';
        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('note-submit-creation'),
            [
                'message' => $noteMessage,
                'client_id' => $clientRow['id'],
                'is_main' => 0
            ]
        );
        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserData['id']);

        // Make request
        $response = $this->app->handle($request);

        // Assert 201 Created redirect to login url
        self::assertSame($expectedResult['creation'][StatusCodeInterface::class], $response->getStatusCode());

        // Assert database
        // Find freshly inserted note
        $noteDbRow = $this->findLastInsertedTableRow('note');
        // Assert the row column values
        $this->assertTableRow(['message' => $noteMessage, 'is_main' => 0], 'note', (int)$noteDbRow['id']);

        // Assert response
        $expectedResponseJson = [
            'status' => 'success',
            'data' => [
                'userFullName' => $authenticatedUserData['first_name'] . ' ' . $authenticatedUserData['surname'],
                'noteId' => $noteDbRow['id'],
                'createdDateFormatted' => $this->dateTimeToClientReadNoteFormat($noteDbRow['created_at']),
            ],
        ];
        $this->assertJsonData($expectedResponseJson, $response);
    }

    /**
     * Test note creation on client-read page while being unauthenticated.
     *
     * @return void
     */
    public function testClientReadNoteCreation_unauthenticated(): void
    {
        // xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "client/" + clientId);
    }

    /**
     * Test note creation on client-read page while being authenticated.
     *
     * @return void
     */
    public function testClientReadNoteModification(): void
    {
    }

    /**
     * Test note creation on client-read page while being unauthenticated.
     *
     * @return void
     */
    public function testClientReadNoteModification_unauthenticated(): void
    {
    }

    /**
     * Test note creation on client-read page while being authenticated.
     *
     * @return void
     */
    public function testClientReadNoteDeletion(): void
    {
    }

    /**
     * Test note creation on client-read page while being unauthenticated.
     *
     * @return void
     */
    public function testClientReadNoteDeletion_unauthenticated(): void
    {
    }
}