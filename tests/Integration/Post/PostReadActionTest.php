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

class PostReadActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;

    /**
     * User update process with valid data
     *
     * @return void
     */
    public function testPostReadAction(): void
    {
        // Insert linked user to post (only first one to make dynamic expected array)
        $userRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $userRow);
        // Insert post (only first one to make dynamic expected array)
        $postRow = (new PostFixture())->records[0];
        $this->insertFixture('post', $postRow);

        // Simulate logged in user with id 1
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('post-read', ['post_id' => 1])
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert response data
        $this->assertJsonData(
            [
                // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
                'postId' => $postRow['id'],
                'userId' => $userRow['id'],
                'postMessage' => $postRow['message'],
                'postCreatedAt' => $postRow['created_at'],
                'postUpdatedAt' => $postRow['updated_at'],
                'userName' => $userRow['name'],
                'userRole' => $userRow['role'],
            ],
            $response
        );
    }

}