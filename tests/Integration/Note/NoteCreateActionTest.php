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
use IntlDateFormatter;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpJsonTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

/**
 * Test cases for client read note creation
 *  - Authenticated with different user roles
 *  - Unauthenticated
 *  - Invalid data (validation test).
 */
class NoteCreateActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Test main note and normal note update on client-read page while being authenticated
     * with different user roles.
     *
     * @param array $linkedUserRow client owner attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\Note\NoteProvider::class, 'noteCreateUpdateDeleteProvider')]
    public function testNoteSubmitCreateActionAuthorization(
        array $linkedUserRow,
        array $authenticatedUserRow,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixturesWithAttributes($authenticatedUserRow, $linkedUserRow);

        // Insert needed client status fixture
        $clientStatusId = $this->insertFixture(ClientStatusFixture::class)['id'];
        // Insert one client linked to this user
        $clientRow = $this->insertFixture(
            ClientFixture::class,
            ['user_id' => $linkedUserRow['id'], 'client_status_id' => $clientStatusId],
        );

        // Create request
        $noteMessage = 'Test note';
        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('note-create-submit'),
            [
                'message' => $noteMessage,
                'client_id' => $clientRow['id'],
                'is_main' => 0,
            ]
        );
        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        // Make request
        $response = $this->app->handle($request);

        // Assert 201 Created redirect to login url
        self::assertSame($expectedResult['creation'][StatusCodeInterface::class], $response->getStatusCode());

        // Assert database
        // Find freshly inserted note
        $noteDbRow = $this->findLastInsertedTableRow('note');
        // Assert the row column values
        $this->assertTableRow(['message' => $noteMessage, 'is_main' => 0], 'note', (int)$noteDbRow['id']);
        // Assert that user activity is inserted
        $this->assertTableRow(
            [
                'action' => UserActivity::CREATED->value,
                'table' => 'note',
                'row_id' => $noteDbRow['id'],
                'data' => json_encode([
                    'message' => $noteMessage,
                    'client_id' => $clientRow['id'],
                    'is_main' => 0,
                    'user_id' => $authenticatedUserRow['id'],
                ], JSON_THROW_ON_ERROR),
            ],
            'user_activity',
            (int)$this->findLastInsertedTableRow('user_activity')['id']
        );
        $dateFormatter = new IntlDateFormatter(
            setlocale(LC_ALL, 0) ?: null,
            IntlDateFormatter::LONG,
            IntlDateFormatter::SHORT
        );

        // Assert response
        $expectedResponseJson = [
            'status' => 'success',
            'data' => [
                'userFullName' => $authenticatedUserRow['first_name'] . ' ' . $authenticatedUserRow['surname'],
                'noteId' => $noteDbRow['id'],
                'createdDateFormatted' => $dateFormatter->format(new \DateTime($noteDbRow['created_at'])),
            ],
        ];
        $this->assertJsonData($expectedResponseJson, $response);
    }

    /**
     * Test note creation on client-read page while being unauthenticated.
     *
     * @return void
     */
    public function testNoteSubmitCreateActionUnauthenticated(): void
    {
        $request = $this->createJsonRequest('POST', $this->urlFor('note-create-submit'));
        // Make request
        $response = $this->app->handle($request);
        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $this->urlFor('login-page')], $response);
    }

    /**
     * Test note creation on client-read page with invalid data.
     *
     * @param array $invalidRequestBody
     * @param bool $existingMainNote
     * @param array $expectedResponseData
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\Note\NoteProvider::class, 'invalidNoteCreationProvider')]
    public function testNoteCreateSubmitActionInvalid(
        array $invalidRequestBody,
        bool $existingMainNote,
        array $expectedResponseData
    ): void {
        // Insert user authorized to create
        $clientOwnerId = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::ADVISOR]),
        )['id'];
        // Insert linked status
        $clientStatusId = $this->insertFixture(ClientStatusFixture::class)['id'];
        // Insert client row
        $clientRow = $this->insertFixture(
            ClientFixture::class,
            ['user_id' => $clientOwnerId, 'client_status_id' => $clientStatusId],
        );

        // Insert main note linked to client and user if data provider $existingMainNote is true
        if ($existingMainNote === true) {
            // Creating main note row with correct values
            $mainNoteRow = (new NoteFixture())->records[0];
            $mainNoteRow['is_main'] = 1;
            $mainNoteRow['client_id'] = $clientRow['id'];
            $mainNoteRow['user_id'] = $clientOwnerId;
            $this->insertFixtureRow('note', $mainNoteRow);
        }

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $clientOwnerId);

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('note-create-submit'),
            $invalidRequestBody
        );
        $response = $this->app->handle($request);

        // Assert 422 Unprocessable entity
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // Assert json response data
        $this->assertJsonData($expectedResponseData, $response);
    }
}
