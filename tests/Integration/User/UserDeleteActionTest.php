<?php


namespace App\Test\Integration\User;


use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\PostFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\DatabaseExtensionTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use App\Test\Traits\RouteTestTrait;

class UserDeleteActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;

    /**
     * Test user delete its own account
     *
     * @return void
     */
    public function testUserDeleteAction(): void
    {
        // Insert logged-in user with id 2 role: user
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);
        $loggedInUserId = $userRow['id']; // 2

        // Insert post with id 2 (to match ownership of user 2)
        $postRow = (new PostFixture())->records[1];
        $this->insertFixture('post', $postRow);
        $postId = $postRow['id']; // 2

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'DELETE',
            // User delete route with id like /users/1
            $this->urlFor('user-delete-submit', ['user_id' => $loggedInUserId]),
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that response content type is json
        $this->assertJsonContentType($response);

        // Check that user is deleted with assertNotNull as I can't pass "not-null" to assertTableRowValue()
        self::assertNotNull($this->getTableRowById('user', $userRow['id'], ['deleted_at'])['deleted_at']);

        // Check that all posts associated to user are also marked as deleted
        $dbPosts = $this->findTableRowsByColumn('post', 'user_id', $userRow['id'], ['deleted_at']);
        foreach ($dbPosts as $post) {
            self::assertNotNull($post['deleted_at']);
        }
    }

    /**
     * Test that user with role admin can delete other user
     *
     * @return void
     */
    public function testUserDeleteAction_otherUserAsAdmin(): void
    {
        // Insert logged-in user with id 1 role: admin
        $adminRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $adminRow);
        $loggedInUserId = $adminRow['id'];

        // Insert other user
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);
        $userIdToDelete = $userRow['id'];

        // Insert post with id 2 (to match ownership of user 2)
        $postRow = (new PostFixture())->records[1];
        $this->insertFixture('post', $postRow);

        // Simulate logged-in admin
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('user-delete-submit', ['user_id' => $userIdToDelete]),
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that response content type is json
        $this->assertJsonContentType($response);

        // Check that user is deleted
        self::assertNotNull($this->getTableRowById('user', $userIdToDelete, ['deleted_at'])['deleted_at']);

        // Check that all posts associated to user are also marked as deleted
        $dbPosts = $this->findTableRowsByColumn('post', 'user_id', $userIdToDelete, ['deleted_at']);
        foreach ($dbPosts as $post) {
            self::assertNotNull($post['deleted_at']);
        }
    }

    /**
     * Test that user with role user can't delete other user
     *
     * @return void
     */
    public function testUserDeleteAction_otherUserAsUser(): void
    {
        // Insert user that is linked to post id 1
        $otherUserRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $otherUserRow);
        $otherUserId = $otherUserRow['id'];

        // Insert logged-in user with id 2 role: user
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);
        $loggedInUserId = $userRow['id'];

        // Insert post with id 1 linked to user 1 (to have different ownership)
        $postRow = (new PostFixture())->records[0];
        $this->insertFixture('post', $postRow);

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'DELETE',
            // Make request to delete other user
            $this->urlFor('user-delete-submit', ['user_id' => $otherUserId]),
        );

        $response = $this->app->handle($request);

        // Assert: 403 Forbidden
        self::assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
        // Assert that response has content type json
        $this->assertJsonContentType($response);

        // Assert that user was not deleted
        $this->assertTableRow(['deleted_at' => null], 'user', $otherUserId);

        // Assert that posts linked to user are also not deleted
        $this->assertTableRowsByColumn(['deleted_at' => null], 'post', 'user_id', $otherUserId);
    }

    /**
     * Test that when user is not logged in 401 Unauthorized is returned
     * and that the authentication middleware provides the correct login url
     * if Redirect-to-if-unauthorized header is set
     *
     * @return void
     */
    public function testUserDeleteAction_notLoggedIn(): void
    {
        // Insert user (id 2 role: user)
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);

        // NOT simulate logged-in user

        $request = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('user-delete-submit', ['user_id' => $userRow['id']]),
        );

        // Provide redirect to if unauthorized header to test if UserAuthenticationMiddleware returns correct login url
        $redirectAfterLoginRouteName = 'profile-page';
        $request = $request->withAddedHeader('Redirect-to-if-unauthorized', $redirectAfterLoginRouteName);


        $response = $this->app->handle($request);

        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Build expected login url with redirect query param as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $this->urlFor($redirectAfterLoginRouteName)]
        );

        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);

        // Assert that deleted_at IS (still) null meaning it was not deleted
        $this->assertTableRow(['deleted_at' => null],'user', $userRow['id']);

        // I think it's not necessary to test that posts are not deleted as its already tested that when user
        // can't be deleted, posts are not too
    }

    /**
     * Test request to delete a non-existing user
     * Expected behaviour: if user is an admin the repository functions to delete user are called, but
     * it will not do anything and the response should contain this warning in the body
     *
     * @return void
     */
    public function testPostDeleteAction_adminOnNotExistingUser(): void
    {
        // Insert logged-in user with id 2 role: admin
        $userRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $userRow);
        $loggedInUserId = 1;

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

        $request = $this->createJsonRequest(
            'DELETE',
            // Route to delete non-existing user
            $this->urlFor('user-delete-submit', ['user_id' => 999]),
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK)
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->assertJsonData(['status' => 'warning', 'message' => 'User not deleted.'], $response);
    }


}
