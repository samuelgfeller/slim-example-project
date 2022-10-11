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
use Slim\Exception\HttpBadRequestException;

/**
 * Test cases for client read note creation
 *  - Authenticated with different user roles
 *  - Unauthenticated
 *  - Invalid data (validation test)
 *  - Malformed request body
 */
class ClientReadNoteCreateActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use FixtureTrait;

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
     * Test main note and normal note update on client-read page while being authenticated
     * with different user roles.
     *
     * @dataProvider \App\Test\Provider\Client\ClientReadCaseProvider::provideAuthenticatedAndLinkedUserForNote()
     * @return void
     */
    public function testClientReadNoteCreation(
        array $userLinkedToClientData,
        array $authenticatedUserData,
        array $expectedResult
    ): void {
        $this->insertFixture('user', $userLinkedToClientData);
        // If authenticated user and user that should be linked to client is different, insert authenticated user
        if ($userLinkedToClientData['id'] !== $authenticatedUserData['id']) {
            $this->insertFixture('user', $authenticatedUserData);
        }

        // Insert one client linked to this user
        $clientRow = $this->findRecordsFromFixtureWhere(['user_id' => $userLinkedToClientData['id']],
            ClientFixture::class)[0];
        // Insert needed client status fixture
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
        $request = $this->createJsonRequest('POST', $this->urlFor('note-submit-creation'));
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
     * Test note creation on client-read page with invalid data.
     * Fixture dependencies:
     *   - 1 client
     *   - 1 user linked to client
     *
     * @dataProvider \App\Test\Provider\Client\ClientReadCaseProvider::provideInvalidNoteAndExpectedResponseDataForCreation()
     * @return void
     */
    public function testClientReadNoteCreation_invalid(
        array $invalidRequestBody,
        bool $existingMainNote,
        array $expectedResponseData
    ): void {
        // Add the minimal needed data
        $clientData = (new ClientFixture())->records[0];
        // Insert user linked to client and user that is logged in
        $userData = $this->findRecordsFromFixtureWhere(['id' => $clientData['user_id']], UserFixture::class)[0];
        $this->insertFixture('user', $userData);
        // Insert linked status
        $this->insertFixtureWhere(['id' => $clientData['client_status_id']], ClientStatusFixture::class);
        // Insert client
        $this->insertFixture('client', $clientData);
        // Insert main note linked to client and user if data provider $existingMainNote is true
        if ($existingMainNote === true) {
            $this->insertFixtureWhere(['is_main' => 1, 'client_id' => $clientData['id'], 'user_id' => $userData['id']],
                NoteFixture::class);
        }

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userData['id']);

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('note-submit-creation'),
            $invalidRequestBody
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
     * @dataProvider \App\Test\Provider\Client\ClientReadCaseProvider::provideNoteCreationMalformedRequestBody()
     * @return void
     */
    public function testClientReadNoteCreation_malformedRequest(array $malformedRequestBody): void
    {
        // Action class should directly return error so only logged-in user has to be inserted
        $userData = (new UserFixture())->records[0];
        $this->insertFixture('user', $userData);

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userData['id']);

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('note-submit-creation'),
            $malformedRequestBody
        );
        // Bad Request (400) means that the client sent the request wrongly; it's a client error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Request body malformed.');

        // Handle request after defining expected exceptions
        $this->app->handle($request);
    }
}