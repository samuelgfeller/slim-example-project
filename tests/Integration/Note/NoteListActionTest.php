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
     * Different privileges of notes depending on authenticated user and
     * note owner are tested with the provider.
     * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideUsersNotesAndExpectedResultForList()
     *
     * @param array $noteOwnerUserData
     * @param array $authenticatedUserData
     * @param array $expectedResult
     * @return void
     */
    public function testNoteListAction(
        array $noteOwnerUserData,
        array $authenticatedUserData,
        array $expectedResult
    ): void {
        // Insert authenticated user
        $authenticatedUserData['id'] = (int)$this->insertFixture('user', $authenticatedUserData);
        // If authenticated user and user that should be linked to note is different, insert both
        if ($noteOwnerUserData['user_role_id'] !== $authenticatedUserData['user_role_id']) {
            $noteOwnerUserData['id'] = (int)$this->insertFixture('user', $noteOwnerUserData);
        } else {
            $noteOwnerUserData['id'] = $authenticatedUserData['id'];
        }

        // As the client owner is not relevant, another user (advisor) is taken. If this test fails in the future
        // because note read rights change (e.g. that newcomers may not see the notes from everyone), the
        // client owner id has to be added to the provider
        $clientOwnerId = $this->insertFixturesWithAttributes(['user_role_id' => 3], UserFixture::class)['id'];
        // Get client row
        $clientRow = $this->getFixtureRecordsWithAttributes(['user_id' => $clientOwnerId], ClientFixture::class);
        // Insert linked status (only first one to make dynamic expected array)
        $this->insertFixturesWithAttributes(['id' => $clientRow['client_status_id']], ClientStatusFixture::class);
        // Insert client row after client status
        $clientRow['id'] = (int)$this->insertFixture('client', $clientRow);

        // Insert linked note. Only one per test to simplify assertions with different privileges
        $noteData = $this->insertFixturesWithAttributes(
            ['is_main' => 0, 'client_id' => $clientRow['id'], 'user_id' => $noteOwnerUserData['id'], 'deleted_at' => null],
            NoteFixture::class
        );

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserData['id']);
        // Make request
        $request = $this->createJsonRequest('GET', $this->urlFor('note-list'))->withQueryParams(['client_id' => 1]);
        $response = $this->app->handle($request);

        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        $expectedResponseArray[] = [
            // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
            'noteId' => $noteData['id'],
            'noteMessage' => $noteData['message'],
            // Same format as in NoteFinder:findAllNotesFromClientExceptMain()
            'noteCreatedAt' => (new \DateTime($noteData['created_at']))->format('d. F Y • H:i'),
            'noteUpdatedAt' => (new \DateTime($noteData['updated_at']))->format('d. F Y • H:i'),
            'userId' => $noteData['user_id'],
            'userFullName' => $noteOwnerUserData['first_name'] . ' ' . $noteOwnerUserData['surname'],
            // Has to match user rights rules in NoteUserRightSetter.php
            // Currently don't know the best way to implement this dynamically
            'privilege' => $expectedResult['privilege']->value,
        ];

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