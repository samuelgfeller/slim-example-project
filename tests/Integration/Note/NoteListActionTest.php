<?php

namespace App\Test\Integration\Note;

use App\Domain\Authorization\Privilege;
use App\Domain\User\Enum\UserRole;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\NoteFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\AuthorizationTestTrait;
use App\Test\Traits\DatabaseExtensionTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use IntlDateFormatter;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 *  Test cases for client read note loading
 *   - Authenticated
 *   - Unauthenticated.
 */
class NoteListActionTest extends TestCase
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
     * Tests notes that are loaded with ajax on client read page.
     * One note at a time is tested for the sake of simplicity.
     *
     * @dataProvider \App\Test\Provider\Note\NoteProvider::noteListUserAttributesAndExpectedResultProvider()
     * Different privileges of notes depending on authenticated user and
     * note owner are tested with the provider.
     *
     * @param array $userLinkedToNoteRow note owner attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param int|null $noteHidden 1 or 0 or null if tested note is hidden
     * @param array{privilege: Privilege} $expectedResult privilege
     *
     * @return void
     */
    public function testNoteListActionAuthorization(
        array $userLinkedToNoteRow,
        array $authenticatedUserRow,
        ?int $noteHidden,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixturesWithAttributes($userLinkedToNoteRow, $authenticatedUserRow);

        // As the client owner is not relevant, another user (advisor) is taken. If this test fails in the future
        // because note read rights change (e.g. that newcomers may not see the notes from everyone), the
        // client owner id has to be added to the provider
        $clientOwnerId = $this->insertFixturesWithAttributes(
            $this->addUserRoleId(['user_role_id' => UserRole::ADVISOR]),
            UserFixture::class
        )['id'];
        // Insert linked status
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];

        // Insert client row
        $clientRow = $this->insertFixturesWithAttributes(
            ['user_id' => $clientOwnerId, 'client_status_id' => $clientStatusId],
            ClientFixture::class
        );

        // Insert linked note. Only one per test to simplify assertions with different privileges
        $noteData = $this->insertFixturesWithAttributes(
            [
                'is_main' => 0,
                'client_id' => $clientRow['id'],
                'user_id' => $userLinkedToNoteRow['id'],
                'hidden' => $noteHidden,
            ],
            NoteFixture::class
        );

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
        // Make request
        $request = $this->createJsonRequest('GET', $this->urlFor('note-list'))->withQueryParams(['client_id' => 1]);
        $response = $this->app->handle($request);

        // Assert status code
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // If user has not privilege to read note, the message is replaced by lorem ipsum
        $loremIpsum = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor 
invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo 
duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit 
amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt 
ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores 
et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';

        $dateFormatter = new IntlDateFormatter(setlocale(LC_ALL, 0), IntlDateFormatter::LONG, IntlDateFormatter::SHORT);

        $expectedResponseArray[] = [
            // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
            'id' => $noteData['id'],
            'userId' => $noteData['user_id'],
            'clientId' => $clientRow['id'],
            // Note message either plain text or replaced with lorem ipsum if not allowed to read
            'message' => $expectedResult['privilege'] === Privilege::NONE ?
                substr($loremIpsum, 0, mb_strlen($noteData['message'])) : $noteData['message'],
            'hidden' => $noteHidden,
            // Same format as in NoteFinder:findAllNotesFromClientExceptMain()
            'createdAt' => $dateFormatter->format(new \DateTime($noteData['created_at'])),
            'updatedAt' => $dateFormatter->format(new \DateTime($noteData['updated_at'])),
            'userFullName' => $userLinkedToNoteRow['first_name'] . ' ' . $userLinkedToNoteRow['surname'],
            'clientFullName' => null,
            // Has to match privilege from NoteAuthorizationGetter.php (rules are in NoteAuthorizationChecker.php)
            'privilege' => $expectedResult['privilege']->value,
            'isClientMessage' => 0,
        ];

        // Assert response data
        $this->assertJsonData($expectedResponseArray, $response);
    }

    /**
     * Test note list action with filter.
     *
     * @dataProvider \App\Test\Provider\Note\NoteProvider::noteListWithFilterProvider()
     *
     * @param array $filterQueryParams
     * @param string $expectedNotesWhereString
     * @param array $usersAttrToInsert
     * @param array $clientAttrToInsert
     * @param array $notesAttrToInsert
     *
     * @return void
     */
    public function testNoteListFilter(
        array $filterQueryParams,
        string $expectedNotesWhereString,
        array $usersAttrToInsert,
        array $clientAttrToInsert,
        array $notesAttrToInsert,
    ): void {
        // Authenticated user role not relevant here, and it should not cause issues (authorization tested above)
        $loggedInUserId = $this->insertFixturesWithAttributes(
            $this->addUserRoleId(['user_role_id' => UserRole::MANAGING_ADVISOR]),
            UserFixture::class
        )['id'];

        // Insert users without specific user role
        $users = $this->insertFixturesWithAttributes($usersAttrToInsert, UserFixture::class);
        // Insert client status and client
        $statusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
        $clientAttrToInsert['client_status_id'] = $statusId;
        $clientId = $this->insertFixturesWithAttributes($clientAttrToInsert, ClientFixture::class)['id'];
        $notes = $this->insertFixturesWithAttributes($notesAttrToInsert, NoteFixture::class);

        // Add session
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('note-list', [], $filterQueryParams)
        );

        $response = $this->app->handle($request);

        // Assert status code
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Filter inserted records with given row filter params
        $noteRows = $this->findTableRowsWhere('note', $expectedNotesWhereString);

        // Create expected array based on fixture records
        $expected = [];
        foreach ($noteRows as $noteRow) {
            // Add clients to expected array
            $expected[] = [
                // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
                'id' => $noteRow['id'],
                'clientId' => $noteRow['client_id'],
                'hidden' => $noteRow['hidden'],
                'message' => $noteRow['message'],
                'userId' => $noteRow['user_id'],
            ];
        }

        // Get response json data
        $responseJson = $this->getJsonData($response);
        // Remove keys from response json that are not asserted in this test
        foreach ($responseJson as $key => $noteFromResponse) {
            // Replace notes from response array with the same values except keys that are not in the expected array
            // $expected[0] is taken as an example of which keys should be kept in $noteFromResponse
            $responseJson[$key] = array_intersect_key($noteFromResponse, $expected[0] ?? []);
        }
        // Assert equals without taking the order of the array elements in account
        self::assertEqualsCanonicalizing($expected, $responseJson);
    }

    /**
     * Note list filters require the value to be in a specific format
     * (e.g. numeric) otherwise an exception should be thrown. This is
     * tested here.
     *
     * @dataProvider \App\Test\Provider\Note\NoteProvider::invalidNoteListFilterProvider()
     *
     * @param array $filterQueryParams
     * @param string $exceptionMessage
     *
     * @return void
     */
    public function testNoteListFilterInvalid(
        array $filterQueryParams,
        string $exceptionMessage,
    ): void {
        $loggedInUserId = $this->insertFixturesWithAttributes([], UserFixture::class)['id'];
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('note-list', [], $filterQueryParams)
        );

        $response = $this->app->handle($request);

        // Assert response HTTP status code: 422
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $message = $this->getJsonData($response)['message'];
        self::assertSame($exceptionMessage, $message);
    }

    /**
     * Test when note-list request is made from client-read page
     * without being authenticated.
     *
     * @return void
     */
    public function testClientReadNotesLoadUnauthenticated(): void
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
            'login-page',
            [],
            ['redirect' => $redirectToUrlAfterLogin]
        );
        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }
}
