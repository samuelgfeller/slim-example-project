<?php

namespace App\Test\Integration\Post;

use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\PostFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\FixtureTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use App\Test\Traits\RouteTestTrait;
use Slim\Exception\HttpBadRequestException;

/**
 * Integration testing user update Process
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
     * User update process with valid data
     * Fixtures dependency:
     *      UserFixture: one user with id 1
     *      PostFixture: one post with user_id 1 (better if at least two)
     *
     * @return void
     */
    public function testPostListAction(): void
    {
        // All user fixtures required to insert all post fixtures
        $this->insertFixtures([UserFixture::class, PostFixture::class]);

        // Simulate logged in user with id 1
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('post-list-own')
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Create expected array based on fixture records
        $expected = [];
        // Get rows of posts linked to user 1
        $postsFromUserRecords = $this->findRecordsFromFixtureWhere(['user_id' => 1], PostFixture::class);
        // Relevant user record
        $userRow = $this->findRecordsFromFixtureWhere(['id' => 1], UserFixture::class)[0];
        foreach ($postsFromUserRecords as $postRow) {
            $expected[] = [
                // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
                'postId' => $postRow['id'],
                'userId' => $userRow['id'],
                'postMessage' => $postRow['message'],
                'postCreatedAt' => $postRow['created_at'],
                'postUpdatedAt' => $postRow['updated_at'],
                'userName' => $userRow['name'],
                'userRole' => $userRow['role'],
            ];
        }

        $this->assertJsonData($expected, $response);
    }

    public function testPostListAction_notLoggedIn(): void
    {
        $request = $this->createJsonRequest('GET',$this->urlFor('post-list-own'));

        $response = $this->app->handle($request);
        // Before it even accesses the action class, the UserAuthMiddleware catches the request and redirects to login
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
        // Assert that it redirected to the login page with correct redirect get param back to own-posts
        self::assertSame(
            $this->urlFor('login-page', [], ['redirect' => $this->urlFor('post-list-own')]),
            $response->getHeaderLine('Location')
        );
    }

}