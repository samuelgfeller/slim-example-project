<?php

namespace App\Test\Integration\Note;

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
use Slim\Exception\HttpBadRequestException;


/**
 * Test cases for client read note modification
 *  - Authenticated with different user roles
 *  - Unauthenticated
 *  - Invalid data (validation test)
 *  - Malformed request body
 */
class NoteUpdateActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use FixtureTrait;

    /**
     * Test note modification on client-read page while being authenticated
     * with different user roles.
     *
     * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideUserAttributesAndExpectedResultForNoteCUD()
     * @param array $userLinkedToNoteAttr note owner attributes containing the user_role_id
     * @param array $authenticatedUserAttr authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
     * @return void
     */
    public function testNoteSubmitUpdateAction(
        array $userLinkedToNoteAttr,
        array $authenticatedUserAttr,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $authenticatedUserRow = $this->insertFixturesWithAttributes($authenticatedUserAttr, UserFixture::class);
        if ($authenticatedUserAttr === $userLinkedToNoteAttr) {
            $userLinkedToNoteRow = $authenticatedUserRow;
        }else{
            // If authenticated user and owner user is not the same, insert owner
            $userLinkedToNoteRow = $this->insertFixturesWithAttributes($userLinkedToNoteAttr, UserFixture::class);
        }

        // Insert linked status
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
        // Insert one client linked to this user
        $clientRow = $this->insertFixturesWithAttributes(
            ['user_id' => $userLinkedToNoteRow['id'], 'client_status_id' => $clientStatusId],
            ClientFixture::class
        );

        // Insert main note attached to client and given "owner" user
        $mainNoteRow = $this->insertFixturesWithAttributes(
            ['is_main' => 1, 'user_id' => $userLinkedToNoteRow['id'], 'client_id' => $clientRow['id']],
            NoteFixture::class
        );

        // Insert normal note attached to client and given "owner" user
        $normalNoteRow = $this->insertFixturesWithAttributes(
            ['is_main' => 0, 'user_id' => $userLinkedToNoteRow['id'], 'client_id' => $clientRow['id']],
            NoteFixture::class
        );

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $newNoteMessage = 'New note message';
        // --- *MAIN note request ---
        // Create request to edit main note
        $mainNoteRequest = $this->createJsonRequest(
            'PUT', $this->urlFor('note-submit-modification', ['note_id' => $mainNoteRow['id']]),
            ['message' => $newNoteMessage,]
        );
        // Make request
        $mainNoteResponse = $this->app->handle($mainNoteRequest);

        // Assert 200 OK note updated successfully
        self::assertSame(
            $expectedResult['modification']['main_note'][StatusCodeInterface::class],
            $mainNoteResponse->getStatusCode()
        );

        if ($expectedResult['modification']['main_note']['db_changed'] === true) {
            $this->assertTableRow(['message' => $newNoteMessage], 'note', $mainNoteRow['id']);
        } else {
            // If db is not expected to change message should remain the same as when it was inserted first
            $this->assertTableRow(['message' => $mainNoteRow['message']], 'note', $mainNoteRow['id']);
        }

        // Assert response
        $this->assertJsonData($expectedResult['modification']['main_note']['json_response'], $mainNoteResponse);

        // --- *NORMAL NOTE REQUEST ---
        $normalNoteRequest = $this->createJsonRequest(
            'PUT', $this->urlFor('note-submit-modification', ['note_id' => $normalNoteRow['id']]),
            ['message' => $newNoteMessage,]
        );
        // Make request
        $normalNoteResponse = $this->app->handle($normalNoteRequest);
        self::assertSame(
            $expectedResult['modification']['normal_note'][StatusCodeInterface::class],
            $normalNoteResponse->getStatusCode()
        );

        // If db is expected to change assert the new message
        if ($expectedResult['modification']['normal_note']['db_changed'] === true) {
            $this->assertTableRow(['message' => $newNoteMessage], 'note', $normalNoteRow['id']);
        } else {
            // If db is not expected to change message should remain the same as when it was inserted first
            $this->assertTableRow(['message' => $normalNoteRow['message']], 'note', $normalNoteRow['id']);
        }

        $this->assertJsonData($expectedResult['modification']['normal_note']['json_response'], $normalNoteResponse);
    }

    /**
     * Test note modification on client-read page while being unauthenticated.
     *
     * @return void
     */
    public function testNoteSubmitUpdateAction_unauthenticated(): void
    {
        $request = $this->createJsonRequest(
            'PUT', $this->urlFor('note-submit-modification', ['note_id' => 1]),
            ['message' => 'New test message',]
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

    /**
     * Test note modification on client-read page with invalid data.
     * Fixture dependencies:
     *   - 1 client
     *   - 1 user linked to client
     *   - 1 note that is linked to the client and the user
     *
     * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideInvalidNoteAndExpectedResponseDataForModification()
     * @return void
     */
    public function testNoteSubmitUpdateAction_invalid(string $invalidMessage, array $expectedResponseData): void
    {
        // Insert authorized user
        $userId = $this->insertFixturesWithAttributes(['user_role_id' => 3], UserFixture::class)['id'];
        // Insert linked status
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
        // Insert client row
        $clientRow = $this->insertFixturesWithAttributes(
            ['user_id' => $userId, 'client_status_id' => $clientStatusId],
            ClientFixture::class
        );

        // Insert note linked to client and user
        $noteData = $this->insertFixturesWithAttributes(
            ['client_id' => $clientRow['id'], 'user_id' => $userId],
            NoteFixture::class
        );

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest(
            'PUT', $this->urlFor('note-submit-modification', ['note_id' => $noteData['id']]),
            ['message' => $invalidMessage]
        );
        $response = $this->app->handle($request);

        // Assert 422 Unprocessable entity
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // Assert json response data
        $this->assertJsonData($expectedResponseData, $response);
    }

    /**
     * Test client read note modification with malformed request body
     *
     * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideMalformedNoteRequestBody()
     * @return void
     */
    public function testNoteSubmitUpdateAction_malformedRequest(array $malformedRequestBody): void
    {
        // Action class should directly return error so only logged-in user has to be inserted
        $userData = $this->insertFixturesWithAttributes(['deleted_at' => null], UserFixture::class);

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userData['id']);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('note-submit-modification', ['note_id' => 1]),
            $malformedRequestBody
        );
        // Bad Request (400) means that the client sent the request wrongly; it's a client error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Request body malformed.');

        // Handle request after defining expected exceptions
        $this->app->handle($request);
    }
}