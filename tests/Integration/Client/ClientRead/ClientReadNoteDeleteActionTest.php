<?php

namespace App\Test\Integration\Client\ClientRead;

use App\Domain\User\Data\MutationRights;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\NoteFixture;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use \App\Test\Traits\AppTestTrait;
use \Selective\TestTrait\Traits\HttpTestTrait;
use \Selective\TestTrait\Traits\HttpJsonTestTrait;
use \Selective\TestTrait\Traits\RouteTestTrait;
use \Selective\TestTrait\Traits\DatabaseTestTrait;
use \App\Test\Traits\DatabaseExtensionTestTrait;
use \App\Test\Fixture\FixtureTrait;
use Slim\Exception\HttpMethodNotAllowedException;

/**
 * Test cases for client read note deletion
 *  - Authenticated with different user roles
 *  - Unauthenticated
 */
class ClientReadNoteDeleteActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use FixtureTrait;

    /**
     * Test normal and main note deletion on client-read page
     * while being authenticated.
     *
     * @dataProvider \App\Test\Provider\Client\ClientReadCaseProvider::provideUsersAndExpectedResultForNoteMutation()
     * @return void
     */
    public function testClientReadNoteDeletion(
        array $userLinkedToNoteData,
        array $authenticatedUserData,
        array $expectedResult
    ): void {
        $this->insertFixture('user', $userLinkedToNoteData);
        // If authenticated user and user that is linked to client is different, insert authenticated user
        if ($userLinkedToNoteData['id'] !== $authenticatedUserData['id']) {
            $this->insertFixture('user', $authenticatedUserData);
        }
        // Insert one client linked to this user
        $clientRow = $this->findRecordsFromFixtureWhere(['user_id' => $userLinkedToNoteData['id']],
            ClientFixture::class)[0];
        // In array first to assert user data later
        $this->insertFixtureWhere(['id' => $clientRow['client_status_id']], ClientStatusFixture::class);
        $this->insertFixture('client', $clientRow);

        // Insert main note attached to client and given "owner" user
        $mainNoteData = $this->findRecordsFromFixtureWhere(
            [
                'is_main' => 1,
                'user_id' => $userLinkedToNoteData['id'],
                'client_id' => $clientRow['id'],
                'deleted_at' => null
            ],
            NoteFixture::class
        )[0];
        $this->insertFixture('note', $mainNoteData);
        // Insert normal note attached to client and given "owner" user
        $normalNoteData = $this->findRecordsFromFixtureWhere(
            [
                'is_main' => 0,
                'user_id' => $userLinkedToNoteData['id'],
                'client_id' => $clientRow['id'],
                'deleted_at' => null
            ],
            NoteFixture::class
        )[0];
        $this->insertFixture('note', $normalNoteData);

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserData['id']);

        // --- *MAIN note request ---
        // Create request to edit main note
        $mainNoteRequest = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('note-submit-delete', ['note_id' => $mainNoteData['id']]),
        );

        // As deleting the main note is not a valid request the server throws an HttpMethodNotAllowed exception
        $this->expectException(HttpMethodNotAllowedException::class);
        $this->expectExceptionMessage('The main note cannot be deleted.');

        // Make request
        $this->app->handle($mainNoteRequest);

        // Database is not expected to change for the main note as there is no way to delete it from the frontend
        $this->assertTableRow(['deleted_at' => null], 'note', $mainNoteData['id']);


        // --- *NORMAL NOTE REQUEST ---
        $normalNoteRequest = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('note-submit-delete', ['note_id' => $normalNoteData['id']]),
        );
        // Make request
        $normalNoteResponse = $this->app->handle($normalNoteRequest);
        self::assertSame(
            $expectedResult['deletion']['normal_note'][StatusCodeInterface::class],
            $normalNoteResponse->getStatusCode()
        );

        // Assert database
        $noteDeletedAtValue = $this->findTableRowById('note', $normalNoteData['id'])['deleted_at'];
        // If db is expected to change assert the new message (when provided authenticated user is allowed to do action)
        if ($expectedResult['deletion']['normal_note']['db_changed'] === true) {
            // Test that deleted at is not null
            self::assertNotNull($noteDeletedAtValue);
        } else {
            // If db is not expected to change message should remain the same as when it was inserted first
            self::assertNull($noteDeletedAtValue);
        }

        $this->assertJsonData($expectedResult['deletion']['normal_note']['json_response'], $normalNoteResponse);
    }

    /**
     * Test note deletion on client-read page while being unauthenticated.
     *
     * @return void
     */
    public function testClientReadNoteDeletion_unauthenticated(): void
    {
        $request = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('note-submit-delete', ['note_id' => 1]),
        );
        // Create url where client should be redirected to after login
        $redirectToUrlAfterLogin = $this->urlFor('client-read-page', ['client_id' => 1]);
        $request = $request->withAddedHeader('Redirect-to-url-if-unauthorized', $redirectToUrlAfterLogin);
        // Make request
        $response = $this->app->handle($request);
        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $redirectToUrlAfterLogin]);
        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }
}