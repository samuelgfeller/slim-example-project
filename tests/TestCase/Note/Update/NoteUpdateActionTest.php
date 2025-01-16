<?php

namespace App\Test\TestCase\Note\Update;

use App\Module\User\Enum\UserActivity;
use App\Module\User\Enum\UserRole;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\NoteFixture;
use App\Test\Fixture\UserFixture;
use App\Test\TestCase\Note\Provider\NoteCreateUpdateDeleteProvider;
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
 * Test cases for client read note modification
 *  - Authenticated with different user roles
 *  - Unauthenticated
 *  - Invalid data.
 */
class NoteUpdateActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Test note modification on client-read page while being authenticated
     * with different user roles.
     *
     * @param array $linkedUserRow note owner attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
     *
     * @return void
     */
    #[DataProviderExternal(NoteCreateUpdateDeleteProvider::class, 'noteCreateUpdateDeleteProvider')]
    public function testNoteSubmitUpdateActionAuthorization(
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
        $mainNoteRow = $this->insertFixture(
            NoteFixture::class,
            ['is_main' => 1, 'user_id' => $linkedUserRow['id'], 'client_id' => $clientRow['id']],
        );

        // Insert normal non-hidden note attached to client and given "owner" user
        $normalNoteRow = $this->insertFixture(
            NoteFixture::class,
            ['is_main' => 0, 'user_id' => $linkedUserRow['id'], 'client_id' => $clientRow['id'], 'hidden' => 0],
        );

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $newNoteMessage = 'New note message';
        // --- *MAIN note request ---
        // Create request to edit main note
        $mainNoteRequest = $this->createJsonRequest(
            'PUT',
            $this->urlFor('note-update-submit', ['note_id' => $mainNoteRow['id']]),
            ['message' => $newNoteMessage, 'is_main' => 1]
        );
        // Make request
        $mainNoteResponse = $this->app->handle($mainNoteRequest);

        // Assert 200 OK note updated successfully
        self::assertSame(
            $expectedResult['modification']['mainNote'][StatusCodeInterface::class],
            $mainNoteResponse->getStatusCode()
        );

        if ($expectedResult['modification']['mainNote']['dbChanged'] === true) {
            $this->assertTableRow(['message' => $newNoteMessage], 'note', $mainNoteRow['id']);
        } else {
            // If db is not expected to change message should remain the same as when it was inserted first
            $this->assertTableRow(['message' => $mainNoteRow['message']], 'note', $mainNoteRow['id']);
        }

        // Assert response
        $this->assertJsonData($expectedResult['modification']['mainNote']['jsonResponse'], $mainNoteResponse);

        // --- *NORMAL NOTE REQUEST ---
        $normalNoteRequest = $this->createJsonRequest(
            'PUT',
            $this->urlFor('note-update-submit', ['note_id' => $normalNoteRow['id']]),
            // Change the two values that may be changed
            ['message' => $newNoteMessage, 'hidden' => 1]
        );
        // Make request
        $normalNoteResponse = $this->app->handle($normalNoteRequest);
        self::assertSame(
            $expectedResult['modification']['normalNote'][StatusCodeInterface::class],
            $normalNoteResponse->getStatusCode()
        );

        // If db is expected to change assert the new message
        if ($expectedResult['modification']['normalNote']['dbChanged'] === true) {
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

        $this->assertJsonData($expectedResult['modification']['normalNote']['jsonResponse'], $normalNoteResponse);
    }

    /**
     * Test that if user makes update request but the content is the same
     * as what's in the database, the response contains the warning.
     */
    public function testNoteSubmitUpdateUnchangedContent(): void
    {
        // Insert authorized user
        $userId = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::ADMIN]),
        )['id'];
        // Insert linked client status
        $clientStatusId = $this->insertFixture(ClientStatusFixture::class)['id'];
        // Insert client row
        $clientRow = $this->insertFixture(
            ClientFixture::class,
            ['user_id' => $userId, 'client_status_id' => $clientStatusId],
        );

        // Insert note linked to client and user
        $noteData = $this->insertFixture(
            NoteFixture::class,
            ['client_id' => $clientRow['id'], 'user_id' => $userId, 'is_main' => 0],
        );

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('note-update-submit', ['note_id' => $noteData['id']]),
            ['message' => $noteData['message']]
        );
        $response = $this->app->handle($request);

        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        // Assert that response contains warning
        $this->assertJsonData(['status' => 'warning', 'message' => 'The note was not updated.'], $response);
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
            $this->urlFor('note-update-submit', ['note_id' => '1']),
            ['message' => 'New test message']
        );

        // Make request
        $response = $this->app->handle($request);
        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page');
        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }

    /**
     * Test note modification on client-read page with invalid data.
     *
     * @param array $invalidRequestBody
     * @param array $expectedResponseData
     *
     * @return void
     */
    #[DataProviderExternal(NoteUpdateProvider::class, 'invalidNoteUpdateProvider')]
    public function testNoteSubmitUpdateActionInvalid(array $invalidRequestBody, array $expectedResponseData): void
    {
        // Insert authorized user
        $userId = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::ADVISOR]),
        )['id'];
        // Insert linked status
        $clientStatusId = $this->insertFixture(ClientStatusFixture::class)['id'];
        // Insert client row
        $clientRow = $this->insertFixture(
            ClientFixture::class,
            ['user_id' => $userId, 'client_status_id' => $clientStatusId],
        );

        // Insert note linked to client and user
        $noteData = $this->insertFixture(
            NoteFixture::class,
            ['client_id' => $clientRow['id'], 'user_id' => $userId, 'is_main' => 0],
        );

        // Simulate logged-in user with the same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('note-update-submit', ['note_id' => $noteData['id']]),
            $invalidRequestBody
        );
        $response = $this->app->handle($request);

        // Assert 422 Unprocessable entity
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // Assert json response data
        $this->assertJsonData($expectedResponseData, $response);
    }
}
