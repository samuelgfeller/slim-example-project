<?php

namespace App\Test\Integration\Note;

use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\NoteFixture;
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
use Slim\Exception\HttpMethodNotAllowedException;

/**
 * Test cases for client read note deletion
 *  - Authenticated with different user roles
 *  - Unauthenticated
 */
class NoteDeleteActionTest extends TestCase
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
     * Test normal and main note deletion on client-read page
     * while being authenticated with different user roles.
     *
     * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideUserAttributesAndExpectedResultForNoteCUD()
     * @param array $userLinkedToNoteAttr note owner attributes containing the user_role_id
     * @param array $authenticatedUserAttr authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
     * @return void
     */
    public function testNoteSubmitDeleteAction(
        array $userLinkedToNoteAttr,
        array $authenticatedUserAttr,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $authenticatedUserRow = $this->insertFixturesWithAttributes($this->addUserRoleId($authenticatedUserAttr), UserFixture::class);
        if ($authenticatedUserAttr === $userLinkedToNoteAttr) {
            $userLinkedToNoteRow = $authenticatedUserRow;
        }else{
            // If authenticated user and owner user is not the same, insert owner
            $userLinkedToNoteRow = $this->insertFixturesWithAttributes($this->addUserRoleId($userLinkedToNoteAttr), UserFixture::class);
        }

        // Insert linked status
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
        // Insert one client linked to this user
        $clientRow = $this->insertFixturesWithAttributes(
            ['user_id' => $userLinkedToNoteRow['id'], 'client_status_id' => $clientStatusId],
            ClientFixture::class
        );

        // Insert main note attached to client and given "owner" user
        $mainNoteData = $this->insertFixturesWithAttributes(
            [
                'is_main' => 1,
                'user_id' => $userLinkedToNoteRow['id'],
                'client_id' => $clientRow['id'],
            ],
            NoteFixture::class
        );

        // Insert normal note attached to client and given "owner" user
        $normalNoteData = $this->insertFixturesWithAttributes(
            [
                'is_main' => 0,
                'user_id' => $userLinkedToNoteRow['id'],
                'client_id' => $clientRow['id'],
            ],
            NoteFixture::class
        );

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

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
    public function testNoteSubmitDeleteAction_unauthenticated(): void
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