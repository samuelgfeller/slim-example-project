<?php

namespace App\Test\Integration\Note;

use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Enum\UserRole;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\NoteFixture;
use App\Test\Fixture\UserFixture;
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
 * Test cases for note deletion
 *  - Authenticated with different user roles
 *  - Unauthenticated.
 */
class NoteDeleteActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Test normal and main note deletion on client-read page
     * while being authenticated with different user roles.
     *
     * @param array $linkedUserRow note owner attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\Note\NoteProvider::class, 'noteCreateUpdateDeleteProvider')]
    public function testNoteSubmitDeleteActionAuthorization(
        array $linkedUserRow,
        array $authenticatedUserRow,
        array $expectedResult,
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixtures($authenticatedUserRow, $linkedUserRow);

        // Insert linked status
        $clientStatusId = $this->insertFixture(ClientStatusFixture::class)['id'];
        // Insert one client linked to this user
        $clientRow = $this->insertFixture(
            ClientFixture::class,
            ['user_id' => $linkedUserRow['id'], 'client_status_id' => $clientStatusId],
        );

        // Insert main note attached to client and given "owner" user
        $mainNoteData = $this->insertFixture(
            NoteFixture::class,
            [
                'is_main' => 1,
                'user_id' => $linkedUserRow['id'],
                'client_id' => $clientRow['id'],
            ],
        );

        // Insert normal note attached to client and given "owner" user
        $normalNoteData = $this->insertFixture(
            NoteFixture::class,
            [
                'is_main' => 0,
                'user_id' => $linkedUserRow['id'],
                'client_id' => $clientRow['id'],
            ],
        );

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        // --- *NORMAL NOTE REQUEST ---
        $normalNoteRequest = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('note-delete-submit', ['note_id' => $normalNoteData['id']]),
        );
        // Make request
        $normalNoteResponse = $this->app->handle($normalNoteRequest);
        self::assertSame(
            $expectedResult['deletion']['normalNote'][StatusCodeInterface::class],
            $normalNoteResponse->getStatusCode()
        );

        // Assert database
        $noteDeletedAtValue = $this->findTableRowById('note', $normalNoteData['id'])['deleted_at'];
        // If db is expected to change assert the new message (when provided authenticated user is allowed to do action)
        if ($expectedResult['deletion']['normalNote']['dbChanged'] === true) {
            // Test that deleted at is not null
            self::assertNotNull($noteDeletedAtValue);
            // Assert that user activity is inserted
            $this->assertTableRow(
                [
                    'action' => UserActivity::DELETED->value,
                    'table' => 'note',
                    'row_id' => $normalNoteData['id'],
                    'data' => json_encode(['message' => $normalNoteData['message']]),
                ],
                'user_activity',
                (int)$this->findLastInsertedTableRow('user_activity')['id']
            );
        } else {
            // If db is not expected to change message should remain the same as when it was inserted first
            self::assertNull($noteDeletedAtValue);
            $this->assertTableRowCount(0, 'user_activity');
        }

        $this->assertJsonData($expectedResult['deletion']['normalNote']['jsonResponse'], $normalNoteResponse);

        // --- *MAIN note request ---
        // Create request to edit main note
        $mainNoteRequest = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('note-delete-submit', ['note_id' => $mainNoteData['id']]),
        );

        // Make request
        $invalidOperationResponse = $this->app->handle($mainNoteRequest);

        // As deleting the main note is not a valid request the server responds with a bad request exception
        self::assertSame(StatusCodeInterface::STATUS_BAD_REQUEST, $invalidOperationResponse->getStatusCode());
        // Assert that response contains correct error message
        $this->assertJsonData(
            ['status' => 'error', 'message' => 'The main note cannot be deleted.'],
            $invalidOperationResponse
        );

        // Database is not expected to change for the main note as there is no way to delete it from the frontend
        $this->assertTableRow(['deleted_at' => null], 'note', $mainNoteData['id']);
    }

    public function testNoteSubmitDeleteError(): void
    {
        // Insert authenticated authorized user
        $userRow = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::ADMIN])
        );

        // Not inserting note

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);

        $request = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('note-delete-submit', ['note_id' => '1']),
        );

        $response = $this->app->handle($request);

        // Assert response HTTP status code: 200
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert response json content
        $this->assertJsonData(['status' => 'warning', 'message' => 'Note has not been deleted.'], $response);
    }

    /**
     * Test note deletion on client-read page while being unauthenticated.
     *
     * @return void
     */
    public function testNoteSubmitDeleteActionUnauthenticated(): void
    {
        $request = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('note-delete-submit', ['note_id' => '1']),
        );

        // Make request
        $response = $this->app->handle($request);
        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $this->urlFor('login-page')], $response);
    }
}
