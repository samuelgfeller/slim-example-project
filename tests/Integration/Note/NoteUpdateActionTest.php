<?php

namespace App\Test\Integration\Note;

use App\Domain\User\Enum\UserActivity;
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
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * Test cases for client read note modification
 *  - Authenticated with different user roles
 *  - Unauthenticated
 *  - Invalid data (validation test).
 */
class NoteUpdateActionTest extends TestCase
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
     * Test note modification on client-read page while being authenticated
     * with different user roles.
     *
     * @dataProvider \App\Test\Provider\Note\NoteProvider::noteCreateUpdateDeleteProvider()
     *
     * @param array $userLinkedToNoteRow note owner attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
     *
     * @return void
     */
    public function testNoteSubmitUpdateActionAuthorization(
        array $userLinkedToNoteRow,
        array $authenticatedUserRow,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixturesWithAttributes($userLinkedToNoteRow, $authenticatedUserRow);

        // Insert linked status
        $clientStatusId = $this->insertFixtureWithAttributes(new ClientStatusFixture())['id'];
        // Insert one client linked to this user
        $clientRow = $this->insertFixtureWithAttributes(
            new ClientFixture(),
            ['user_id' => $userLinkedToNoteRow['id'], 'client_status_id' => $clientStatusId],
        );

        // Insert main note attached to client and given "owner" user
        $mainNoteRow = $this->insertFixtureWithAttributes(
            new NoteFixture(),
            ['is_main' => 1, 'user_id' => $userLinkedToNoteRow['id'], 'client_id' => $clientRow['id']],
        );

        // Insert normal non-hidden note attached to client and given "owner" user
        $normalNoteRow = $this->insertFixtureWithAttributes(
            new NoteFixture(),
            ['is_main' => 0, 'user_id' => $userLinkedToNoteRow['id'], 'client_id' => $clientRow['id'], 'hidden' => 0],
        );

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $newNoteMessage = 'New note message';
        // --- *MAIN note request ---
        // Create request to edit main note
        $mainNoteRequest = $this->createJsonRequest(
            'PUT',
            $this->urlFor('note-submit-modification', ['note_id' => $mainNoteRow['id']]),
            ['message' => $newNoteMessage, 'is_main' => 1]
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
            'PUT',
            $this->urlFor('note-submit-modification', ['note_id' => $normalNoteRow['id']]),
            // Change the two values that may be changed
            ['message' => $newNoteMessage, 'hidden' => 1]
        );
        // Make request
        $normalNoteResponse = $this->app->handle($normalNoteRequest);
        self::assertSame(
            $expectedResult['modification']['normal_note'][StatusCodeInterface::class],
            $normalNoteResponse->getStatusCode()
        );

        // If db is expected to change assert the new message
        if ($expectedResult['modification']['normal_note']['db_changed'] === true) {
            $this->assertTableRow(['message' => $newNoteMessage, 'hidden' => 1], 'note', $normalNoteRow['id']);
            // Assert that user activity is inserted
            $this->assertTableRow(
                [
                    'action' => UserActivity::UPDATED->value,
                    'table' => 'note',
                    'row_id' => $normalNoteRow['id'],
                    'data' => json_encode(['message' => $newNoteMessage, 'hidden' => 1], JSON_THROW_ON_ERROR),
                ],
                'user_activity',
                (int)$this->findLastInsertedTableRow('user_activity')['id']
            );
        } else {
            // If db is not expected to change message should remain the same as when it was inserted first
            $this->assertTableRow(
                ['message' => $normalNoteRow['message'], 'hidden' => 0],
                'note',
                $normalNoteRow['id']
            );
        }

        $this->assertJsonData($expectedResult['modification']['normal_note']['json_response'], $normalNoteResponse);
    }

    /**
     * Test note modification on client-read page while being unauthenticated.
     *
     * @return void
     */
    public function testNoteSubmitUpdateActionUnauthenticated(): void
    {
        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('note-submit-modification', ['note_id' => '1']),
            ['message' => 'New test message']
        );
        // Create url where client should be redirected to after login
        $redirectToUrlAfterLogin = $this->urlFor('client-read-page', ['client_id' => '1']);
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
     *
     * @dataProvider \App\Test\Provider\Note\NoteProvider::invalidNoteUpdateProvider()
     *
     * @param array $requestBody
     * @param array $expectedResponseData
     *
     * @return void
     */
    public function testNoteSubmitUpdateActionInvalid(array $requestBody, array $expectedResponseData): void
    {
        // Insert authorized user
        $userId = $this->insertFixtureWithAttributes(
            new UserFixture(),
            $this->addUserRoleId(['user_role_id' => UserRole::ADVISOR]),
        )['id'];
        // Insert linked status
        $clientStatusId = $this->insertFixtureWithAttributes(new ClientStatusFixture())['id'];
        // Insert client row
        $clientRow = $this->insertFixtureWithAttributes(
            new ClientFixture(),
            ['user_id' => $userId, 'client_status_id' => $clientStatusId],
        );

        // Insert note linked to client and user
        $noteData = $this->insertFixtureWithAttributes(
            new NoteFixture(),
            ['client_id' => $clientRow['id'], 'user_id' => $userId, 'is_main' => 0],
        );

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('note-submit-modification', ['note_id' => $noteData['id']]),
            $requestBody
        );
        $response = $this->app->handle($request);

        // Assert 422 Unprocessable entity
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // Assert json response data
        $this->assertJsonData($expectedResponseData, $response);
    }
}
