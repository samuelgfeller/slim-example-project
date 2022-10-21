<?php

namespace App\Test\Integration\Post;

use App\Domain\Post\Data\UserNoteData;
use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\PostFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\RouteTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;

/**
 * - post list own page access while logged-in
 * - post list own page access while not logged-in
 * - post list own json request while logged-in
 * - post list own json request while not logged-in
 */
class PostListOwnActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTrait;

    /**
     * Test if own posts page returns 200 when logged in
     * Fixtures dependency:
     *      UserFixture: one user with id 1 (for session)(better if at least two)
     *
     * @return void
     */
    public function testPostListOwnPageAction_loggedIn(): void
    {
        // Insert logged in user
        $userRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $userRow);

        // Simulate logged-in user with id 1
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $request = $this->createRequest('GET', $this->urlFor('client-list-assigned-to-me-page'));
        $response = $this->app->handle($request);

        // Assert: 200 OK 
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    /**
     * Test that when accessing own posts page while not being logged-in
     * redirects the user to the login page with correct redirect back to own posts page link
     *
     * @return void
     */
    public function testPostListOwnPageAction_notLoggedIn(): void
    {
        $request = $this->createRequest('GET', $this->urlFor('client-list-assigned-to-me-page'));
        $response = $this->app->handle($request);

        // Assert: 302 Found meaning redirect
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Test that redirect link after login is correct
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $this->urlFor('client-list-assigned-to-me-page')]);
        $actualUrl = $response->getHeaderLine('Location');
        self::assertSame($expectedLoginUrl, $actualUrl);
    }

    /**
     * List all posts attached to user
     * Fixtures dependency:
     *      UserFixture: one user with id 1
     *      PostFixture: 2 posts with user_id
     *
     * @return void
     */
    public function testPostListOwnAction(): void
    {
        // Logged-in user role user that requests to see his own posts
        $userId = 2;

        // All user fixtures required to insert all post fixtures
        $this->insertFixtures([UserFixture::class, PostFixture::class]);

        // Simulate logged-in user with id 1
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('post-list')
        )->withQueryParams(['user' => 'session']);

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Get rows of posts linked to user 1
        $postsFromUserRecords = $this->findRecordsFromFixtureWhere(['user_id' => $userId], PostFixture::class);
        // Relevant user record
        $userRow = $this->findRecordsFromFixtureWhere(['id' => $userId], UserFixture::class)[0];
        // Create expected array based on fixture records
        $expected = [];
        foreach ($postsFromUserRecords as $postRow) {
            $expected[] = [
                // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
                'postId' => $postRow['id'],
                'userId' => $userRow['id'],
                'postMessage' => $postRow['message'],
                'postCreatedAt' => $this->changeDateFormat($postRow['created_at']),
                'postUpdatedAt' => $this->changeDateFormat($postRow['updated_at']),
                'userFullName' => $userRow['first_name'] . ' ' . $userRow['surname'],
                'userRole' => $userRow['role'],
                'mutationRights' => UserNoteData::MUTATION_PERMISSION_ALL, // All as its own posts
            ];
        }

        $this->assertJsonData($expected, $response);
    }

    /**
     * Assert that 401 Unauthorized is returned when user tries to
     * access its own posts when not logged in
     *
     * @return void
     */
    public function testPostListOwnAction_notLoggedIn(): void
    {
        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('post-list')
        )->withQueryParams(['user' => 'session']);

        $response = $this->app->handle($request);
        // Assert: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Expected body content
        // Assert that it redirected to the login page with correct redirect get param back to own-posts
        $expectedBody = [
            'loginUrl' => $this->urlFor('login-page', [], ['redirect' => $this->urlFor('client-list-assigned-to-me-page')])
        ];
        $this->assertJsonData($expectedBody, $response);
    }

    /**
     * PostFinder changes the date into the default format in Europe
     *
     * @param string|null $date
     * @return string|null
     */
    private function changeDateFormat(?string $date): ?string
    {
        return $date ? date('d.m.Y H:i:s', strtotime($date)) : null;
    }

}