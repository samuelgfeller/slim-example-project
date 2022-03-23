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

class PostDeleteActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;

    /**
     * Test user delete own post
     *
     * @return void
     */
    public function testPostDeleteAction(): void
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

        $request = $this->createJsonRequest(
            'DELETE',
            // Post delete route with id like /posts/1
            $this->urlFor('post-submit-delete', ['post_id' => $postId]),
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that response content type is json
        $this->assertJsonContentType($response);

        // Get post values from database
        $dbResult = $this->getTableRowById('post', $postId, ['deleted_at']);

        // Assert that deleted_at IS NOT null
        self::assertNotNull($dbResult['deleted_at']);
    }

    /**
     * Test user trying to delete other post
     *
     * @return void
     */
    public function testPostDeleteAction_otherPostAsUser(): void
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

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'DELETE',
            // Post delete route with id like /posts/1
            $this->urlFor('post-submit-delete', ['post_id' => $postId]),
        );

        $response = $this->app->handle($request);

        // Assert: 403 Forbidden
        self::assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
        // Assert that response has content type json
        $this->assertJsonContentType($response);

        // Assert that deleted_at IS (still) null meaning it was not deleted
        self::assertNull($this->getTableRowById('post', $postId, ['deleted_at'])['deleted_at']);
    }

    /**
     * Test admin deleting other post
     *
     * @return void
     */
    public function testPostDeleteAction_otherPostAsAdmin(): void
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

        $request = $this->createJsonRequest(
            'DELETE',
            // Post delete route with id like /posts/1
            $this->urlFor('post-submit-delete', ['post_id' => $postId]),
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that response content type is json
        $this->assertJsonContentType($response);

        // Get post values from database
        $dbResult = $this->getTableRowById('post', $postId, ['deleted_at']);

        // Assert that deleted_at IS NOT null
        self::assertNotNull($dbResult['deleted_at']);
    }

    /**
     * Test that when user is not logged in 401 Unauthorized is returned
     * and that the authentication middleware provides the correct login url
     * if Redirect-to-if-unauthorized header is set
     *
     * @return void
     */
    public function testPostDeleteAction_notLoggedIn(): void
    {
        // Insert user linked to post (id 2 role: user)
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);

        // Insert post with id 2 (to match ownership of user 2)
        $postRow = (new PostFixture())->records[1];
        $this->insertFixture('post', $postRow);
        $postId = 2;

        // NOT simulate logged-in user

        $request = $this->createJsonRequest(
            'DELETE',
            // Post delete route with id like /posts/1
            $this->urlFor('post-submit-delete', ['post_id' => $postId]),
        );

        // Provide redirect to if unauthorized header to test if UserAuthenticationMiddleware returns correct login url
        $redirectAfterLoginRouteName = 'post-list-own-page';
        $request = $request->withAddedHeader('Redirect-to-if-unauthorized', $redirectAfterLoginRouteName);


        $response = $this->app->handle($request);

        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $this->urlFor($redirectAfterLoginRouteName)]
        );

        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);

        // Assert that deleted_at IS (still) null meaning it was not deleted
        self::assertNull($this->getTableRowById('post', $postId, ['deleted_at'])['deleted_at']);
    }

    /**
     * Test request to delete a non-existing post as a user
     * Expected behaviour: the "if" statement checking for the post owner will check against
     * the user_id of PostData which is null by default meaning a ForbiddenException will and
     * should be thrown as user tries to delete something that he doesn't own.
     *
     * @return void
     */
    public function testPostDeleteAction_userOnNotExistingPost(): void
    {
        // Insert logged-in user with id 2 role: user
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);
        $loggedInUserId = 2;

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'DELETE',
            // Post delete route with id like /posts/1
            $this->urlFor('post-submit-delete', ['post_id' => 999]),
        );

        $response = $this->app->handle($request);

        // Assert: 403 Forbidden
        self::assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
        // Assert that response has content type json
        $this->assertJsonContentType($response);
    }

    /**
     * Test request to delete a non-existing post
     * Expected behaviour: if user is an admin deletePost() is called, but
     * it will not do anything and the response should contain this warning
     *
     * @return void
     */
    public function testPostDeleteAction_adminOnNotExistingPost(): void
    {
        // Insert logged-in user with id 2 role: admin
        $userRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $userRow);
        $loggedInUserId = 1;

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'DELETE',
            // Post delete route with id like /posts/1
            $this->urlFor('post-submit-delete', ['post_id' => 999]),
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->assertJsonData(['status' => 'warning', 'message' => 'Post not deleted.'], $response);
    }


}
