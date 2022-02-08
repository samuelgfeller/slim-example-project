<?php

namespace App\Test\Integration\Post;

use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\PostFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\FixtureTrait;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use App\Test\Traits\RouteTestTrait;

/**
 * Integration testing user update Process
 */
class PostListActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTrait;

    /**
     * Request list of all posts
     * Fixtures dependency:
     *      UserFixture: one user with id 1 (for session)(better if at least two)
     *      PostFixture: one post (better if at least two)
     *
     * @return void
     */
    public function testPostListAction(): void
    {
        // All user fixtures required to insert all post fixtures
        $this->insertFixtures([UserFixture::class, PostFixture::class]);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('post-list-all')
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Create expected array based on fixture records
        $expected = [];
        // Get all posts record (inserted previously)
        $postRecords = (new PostFixture())->records;
        foreach ($postRecords as $postRow) {
            // Linked user record
            $userRow = $this->findRecordsFromFixtureWhere(['id' => $postRow['user_id']], UserFixture::class)[0];
            // Build expected array
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

    /**
     * Request list of posts matching given filters
     * Fixtures dependency:
     *      UserFixture: one user with id 1 (for session)(better if at least two)
     *      PostFixture: one post (better if at least two)
     *
     * @dataProvider \App\Test\Provider\Post\PostFilterCaseProvider::provideValidFilter()
     *
     * @param array $queryParams Filter as GET paramets
     * @param array<string, mixed> $recordFilter Filter as record filter like ['col' => 'value']
     * @return void
     */
    public function testPostListAction_withFilters(array $queryParams, array $recordFilter): void
    {
        // All user fixtures required to insert all post fixtures
        $this->insertFixtures([UserFixture::class, PostFixture::class]);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('post-list-all', [], $queryParams)
        ) // Needed until Nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set [SLE-105]
        ->withQueryParams($queryParams);

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Create expected array based on fixture records
        $expected = [];
        // Get posts records matching filter
        $postRecords = $this->findRecordsFromFixtureWhere($recordFilter, PostFixture::class);
        foreach ($postRecords as $postRow) {
            // Linked user record
            $userRow = $this->findRecordsFromFixtureWhere(['id' => $postRow['user_id']], UserFixture::class)[0];
            // Build expected array
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

    /**
     * Request list of posts but with invalid filter
     *
     * @dataProvider \App\Test\Provider\Post\PostFilterCaseProvider::provideInvalidFilter()
     *
     * @param array $queryParams Filter as GET paramets
     * @param array $expectedBody Expected response body
     * @return void
     */
    public function testPostListAction_invalidFilters(array $queryParams, array $expectedBody): void
    {
        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('post-list-all', [], $queryParams)
        ) // Needed until Nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set
        ->withQueryParams($queryParams);

        $response = $this->app->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $this->assertJsonData($expectedBody, $response);
    }
}