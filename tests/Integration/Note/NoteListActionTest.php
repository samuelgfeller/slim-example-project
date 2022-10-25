<?php

namespace App\Test\Integration\Note;

use App\Domain\Authorization\Privilege;
use App\Domain\Note\Authorization\NoteAuthorizationChecker;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\NoteFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\DatabaseExtensionTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;


/**
 *  Test cases for client read note loading
 *   - Authenticated
 *   - Unauthenticated
 */
class NoteListActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use FixtureTrait;


    /**
     * On client read page display, linked notes are loaded with ajax.
     *
     * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideUsersAndExpectedResultForNoteCrud()
     * @return void
     */
    public function testNoteListAction(
        array $userLinkedToClientData,
        array $authenticatedUserData,
        array $expectedResult
    ): void
    {
        // Insert linked users to client and notes
        $authenticatedUserData['id'] = $this->insertFixture('user', $authenticatedUserData);
        // If authenticated user and user that should be linked to client is different, insert both
        if ($userLinkedToClientData['user_role_id'] !== $authenticatedUserData['user_role_id']) {
            $userLinkedToClientData['id'] = $this->insertFixture('user', $userLinkedToClientData);
        } else {
            $userLinkedToClientData['id'] = $authenticatedUserData['id'];
        }

        // Insert linked status (only first one to make dynamic expected array)
        $clientStatusRow = (new ClientStatusFixture())->records[0];
        $this->insertFixture('client_status', $clientStatusRow);
        // Insert client (only first one to make dynamic expected array)
        $clientRow = (new ClientFixture())->records[0];
        $clientRow['id'] = $this->insertFixture('client', $clientRow);
        // Insert only linked notes
        $this->insertFixturesWithAttributes(['client_id' => $clientRow['id']], NoteFixture::class);

        $request = $this->createJsonRequest('GET', $this->urlFor('note-list'))->withQueryParams(['client_id' => 1]);
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserData['id']);

        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Removing first note from $noteRows as it is a main note that must not be in this response
        $noteRows = $this->getFixtureRecordsWithAttributes(
            ['is_main' => 0, 'client_id' => $clientRow['id'], 'deleted_at' => null],
            NoteFixture::class, 3
        );
        // Get logged-in user row to test user rights
        // $loggedInUserRow = $this->findRecordsFromFixtureWhere(['id' => $loggedInUserId], UserFixture::class)[0];

        $this->container->get(NoteAuthorizationChecker::class);

        // Determine which mutation rights user has
        $hasMutationRight = static function (string $role, int $ownerId) use ($authenticatedUserData['id']): string {
            // Basically same as js function userHasMutationRights() in client-read-template-note.html.js
            return $role === 'admin' || $authenticatedUserData['id'] === $ownerId
                ? Privilege::ALL->value : Privilege::NONE->value;
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
                'mutationRights' => $hasMutationRight($authenticatedUserData['user_role_id'], $noteRow['user_id']),
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
}