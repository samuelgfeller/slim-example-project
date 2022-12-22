<?php

namespace App\Test\Integration\Note;

use App\Domain\User\Enum\UserRole;
use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\NoteFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\AuthorizationTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

class NoteReadPageActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Notes don't have an own read page. When accessed
     * there should be a redirect to the client read page
     * scrolling to the note.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return void
     */
    public function testNoteReadPageActionAuthenticated(): void
    {
        // Insert authenticated user newcomer which is allowed to read the page (only his user will load however)
        $userId = $this->insertFixturesWithAttributes(
            $this->addUserRoleId(['user_role_id' => UserRole::NEWCOMER]),
            UserFixture::class
        )['id'];

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        // *Test request on NOT existing note
        $requestNotExistingNote = $this->createRequest('GET', $this->urlFor('note-read-page', ['note_id' => 1]));
        $response = $this->app->handle($requestNotExistingNote);

        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Assert that user is redirected to client-list page if note doesn't exist
        $expectedClientReadUrl = $this->urlFor('client-list-page');
        self::assertSame($expectedClientReadUrl, $response->getHeaderLine('Location'));

        // *Test request on existing note
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
        $clientId = $this->insertFixturesWithAttributes(
            ['client_status_id' => $clientStatusId],
            ClientFixture::class
        )['id'];
        $noteId = $this->insertFixturesWithAttributes(
            ['client_id' => $clientId, 'user_id' => $userId],
            NoteFixture::class
        )['id'];

        $requestExistingNote = $this->createRequest(
            'GET',
            $this->urlFor('note-read-page', ['note_id' => $noteId])
        );
        $response = $this->app->handle($requestExistingNote);

        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Assert that user is redirected to client-list page if note doesn't exist
        $expectedClientReadUrl = $this->urlFor('client-read-page', ['client_id' => $clientId]);
        // Url with added hash
        $expectedClientReadUrl .= "#note-$noteId-container";
        self::assertSame($expectedClientReadUrl, $response->getHeaderLine('Location'));
    }

    /**
     * Test that user has to be logged in to display the page.
     *
     * @return void
     */
    public function testNoteReadPageActionUnauthenticated(): void
    {
        // Request route to client read page while not being logged in
        $requestRoute = $this->urlFor('note-read-page', ['note_id' => 1]);
        $request = $this->createRequest('GET', $requestRoute);
        $response = $this->app->handle($request);
        // Assert 302 Found redirect to login url
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $requestRoute]);
        self::assertSame($expectedLoginUrl, $response->getHeaderLine('Location'));
    }
}
