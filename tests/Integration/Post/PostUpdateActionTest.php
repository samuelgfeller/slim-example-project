<?php


namespace App\Test\Integration\Post;


use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\PostFixture;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use App\Test\Traits\RouteTestTrait;

/**
 * Post update integration test:
 * - normal update
 * - edit other post as user (403 Forbidden)
 * - edit other post as admin
 * - edit request but with the same content as existing (expected warning that nothing changed in response)
 * NOT in this test (not useful enough to me):
 * - edit non-existing post as admin (expected warning that nothing changed)
 * - edit non-existing post as user (expected forbidden exception)
 */
class PostUpdateActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;

    /**
     * Test update post
     *
     * @return void
     */
    public function testPostUpdateAction(): void
    {
        // Insert logged-in user with id 2 role: user
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);
        $loggedInUserId = 2;

        // Insert post with id 2 (to match ownership of user 2)
        $postRow = (new PostFixture())->records[1];
        $this->insertFixture('post', $postRow);
        $postId = 2;

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $newPostMessage = 'This is a changed post content.';

        $request = $this->createJsonRequest(
            'PUT',
            // Post update route with id like /posts/1
            $this->urlFor('post-submit-update', ['post_id' => $postId]),
            ['message' => $newPostMessage]
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that response content type is json
        $this->assertJsonContentType($response);

        $expected = [
            // id is string as CakePHP Database returns always strings: https://stackoverflow.com/a/11913488/9013718
            'id' => (string)$postId,
            'message' => $newPostMessage,
        ];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
        $this->assertTableRow($expected, 'post', $postId, array_keys($expected));
    }

    /**
     * Test user trying to update other post
     *
     * @return void
     */
    public function testPostUpdateAction_otherPostAsUser(): void
    {
        // Insert user that is linked to post id 1
        $userRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $userRow);

        // Insert logged-in user with id 2 role: user
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);
        $loggedInUserId = 2;

        // Insert post with id 1 linked to user 1 (to have different ownership)
        $postRow = (new PostFixture())->records[0];
        $this->insertFixture('post', $postRow);
        $postId = 1;

        // Simulate logged-in user 2 (not owner)
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $newPostMessage = 'This is a changed post content.';

        $request = $this->createJsonRequest(
            'PUT',
            // Post delete route with id like /posts/1
            $this->urlFor('post-submit-update', ['post_id' => $postId]),
            ['message' => $newPostMessage]
        );

        $response = $this->app->handle($request);

        // Assert: 403 Forbidden
        self::assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
        // Assert that response has content type json
        $this->assertJsonContentType($response);

        // Expected is the original post message as it has to be unchanged
        $expected = ['message' => $postRow['message']];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
        $this->assertTableRow($expected, 'post', $postId, array_keys($expected));
    }

    /**
     * Test that admin can update other posts
     *
     * @return void
     */
    public function testPostUpdateAction_otherPostAsAdmin(): void
    {
        // Insert logged-in user with id 1 role: admin
        $userRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $userRow);
        $loggedInUserId = 1;

        // Insert user attached to post
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);

        // Insert post with id 2 (to match ownership of user 2)
        $postRow = (new PostFixture())->records[1];
        $this->insertFixture('post', $postRow);
        $postId = 2;

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $newPostMessage = 'This is a changed post content.';

        $request = $this->createJsonRequest(
            'PUT',
            // Post delete route with id like /posts/1
            $this->urlFor('post-submit-update', ['post_id' => $postId]),
            ['message' => $newPostMessage]
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that response content type is json
        $this->assertJsonContentType($response);

        // Expected is the changed post message
        $expected = ['message' => $newPostMessage];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
        $this->assertTableRow($expected, 'post', $postId, array_keys($expected));
    }

    /**
     * Test that when user is not logged in 401 Unauthorized is returned
     * and that the authentication middleware provides the correct login url
     * if Redirect-to-route-name-if-unauthorized header is set
     *
     * @return void
     */
    public function testPostUpdateAction_notLoggedIn(): void
    {
        // Insert user linked to post (id 2 role: user)
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);

        // Insert post with id 2 (to match ownership of user 2)
        $postRow = (new PostFixture())->records[1];
        $this->insertFixture('post', $postRow);
        $postId = 2;

        // NOT simulate logged-in user

        $newPostMessage = 'This is a changed post content.';

        $request = $this->createJsonRequest(
            'PUT',
            // Post delete route with id like /posts/1
            $this->urlFor('post-submit-update', ['post_id' => $postId]),
            ['message' => $newPostMessage]
        );

        // Provide redirect to if unauthorized header to test if UserAuthenticationMiddleware returns correct login url
        $redirectAfterLoginRouteName = 'client-list-assigned-to-me-page';
        $request = $request->withAddedHeader('Redirect-to-route-name-if-unauthorized', 'client-list-assigned-to-me-page');

        // Make request
        $response = $this->app->handle($request);

        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $this->urlFor($redirectAfterLoginRouteName)]
        );

        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);

        // Expected is the original post message as i   t has to be unchanged
        $expected = ['message' => $postRow['message']];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
        $this->assertTableRow($expected, 'post', $postId, array_keys($expected));
    }

    /**
     * Test that if user makes update request but the content has not changed
     * compared to what's in the database, the response contains the warning.
     *
     * @return void
     */
    public function testPostDeleteAction_sameContentAsExisting(): void
    {
        // Insert logged-in user with id 2 role: user
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);
        $loggedInUserId = 2;

        // Insert post with id 2 (to match ownership of user 2)
        $postRow = (new PostFixture())->records[1];
        $this->insertFixture('post', $postRow);
        $postId = 2;

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        // New post message is EXACTLY THE SAME as what's in the database
        $newPostMessage = $postRow['message'];

        $request = $this->createJsonRequest(
            'PUT',
            // Post update route with id like /posts/1
            $this->urlFor('post-submit-update', ['post_id' => $postId]),
            ['message' => $newPostMessage]
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->assertJsonData(['status' => 'warning', 'message' => 'The post was not updated.'], $response);
    }

    /**
     * The following tests are NOT done here:
     * - User trying to edit post which doesn't exist (expected Forbiddden)
     * - Admin trying to edit post that doesn't exist (expected return false)
     * They are being tested in PostDeleteActionTest and the logic is quite similar,
     * so I don't think its necessary again.
     */
}
