<?php

namespace App\Test\Integration\Note;

use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\NoteFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
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
    use FixtureTestTrait;


    /**
     * Tests notes that are loaded with ajax on client read page.
     *
     * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideUserAttributesAndExpectedResultForNoteList()
     * Different privileges of notes depending on authenticated user and
     * note owner are tested with the provider.
     *
     * @param array $userLinkedToNoteAttr note owner attributes containing the user_role_id
     * @param array $authenticatedUserAttr authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code and privilege
     * @return void
     */
    public function testNoteListAction(
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

        // As the client owner is not relevant, another user (advisor) is taken. If this test fails in the future
        // because note read rights change (e.g. that newcomers may not see the notes from everyone), the
        // client owner id has to be added to the provider
        $clientOwnerId = $this->insertFixturesWithAttributes(['user_role_id' => 3], UserFixture::class)['id'];
        // Insert linked status
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];

        // Insert client row
        $clientRow = $this->insertFixturesWithAttributes(
            ['user_id' => $clientOwnerId, 'client_status_id' => $clientStatusId],
            ClientFixture::class
        );

        // Insert linked note. Only one per test to simplify assertions with different privileges
        $noteData = $this->insertFixturesWithAttributes(
            ['is_main' => 0, 'client_id' => $clientRow['id'], 'user_id' => $userLinkedToNoteRow['id']],
            NoteFixture::class
        );

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
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
            'userFullName' => $userLinkedToNoteRow['first_name'] . ' ' . $userLinkedToNoteRow['surname'],
            // Has to match privilege from NoteAuthorizationGetter.php (rules are in NoteAuthorizationChecker.php)
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