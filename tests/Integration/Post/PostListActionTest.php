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
 * - post list all page access
 * - post list all normal json request
 * - post list filtered
 * - post list with invalid filters
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
     * Test that list all page is 200 OK both logged in or not
     * Fixtures dependency:
     *      UserFixture: one user with id 1 (for session)(better if at least two)
     *
     * @return void
     */
    public function testPostListAllPageAction(): void
    {
        // Test not logged in
        $requestNotLoggedIn = $this->createRequest('GET', $this->urlFor('client-list-all-page'));
        $responseNotLoggedIn = $this->app->handle($requestNotLoggedIn);
        self::assertSame(StatusCodeInterface::STATUS_OK, $responseNotLoggedIn->getStatusCode());

        // Test with active session
        // Insert logged-in user
        $userRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $userRow);

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);

        $requestLoggedIn = $this->createRequest('GET', $this->urlFor('client-list-all-page'));
        $responseLoggedIn = $this->app->handle($requestLoggedIn);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $responseLoggedIn->getStatusCode());
    }

    /**
     * Request list of all posts
     * Fixtures dependency:
     *      UserFixture: one user with id 1 (for session)(better if at least two)
     *      PostFixture: one post (better if at least two)
     *
     * @return void
     */
    public function testPostListAllAction(): void
    {
        // All user fixtures required to insert all post fixtures
        $this->insertFixtures([UserFixture::class, PostFixture::class]);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('post-list')
        );

        // Simulate logged-in user with id 2 role user
        $loggedInUserId = 2;
        $this->container->get(SessionInterface::class)->set('user_id', $loggedInUserId);

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
                'postCreatedAt' => $this->changeDateFormat($postRow['created_at']),
                'postUpdatedAt' => $this->changeDateFormat($postRow['updated_at']),
                'userFullName' => $userRow['first_name'] . ' ' . $userRow['surname'],
                'userRole' => $userRow['role'],
                // If it's the user's own post, all rights but otherwise none
                'userMutationRight' => $loggedInUserId ===
                $postRow['user_id'] ? UserNoteData::MUTATION_PERMISSION_ALL : UserNoteData::MUTATION_PERMISSION_NONE,
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

        $request = $this->createRequest(
            'GET',
            $this->urlFor('post-list', [], $queryParams)
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
                'postCreatedAt' => $this->changeDateFormat($postRow['created_at']),
                'postUpdatedAt' => $this->changeDateFormat($postRow['updated_at']),
                'userFullName' => $userRow['first_name'] . ' ' . $userRow['surname'],
                'userRole' => $userRow['role'],
                'userMutationRight' => UserNoteData::MUTATION_PERMISSION_NONE, // None as not logged in
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
            $this->urlFor('post-list', [], $queryParams)
        ) // Needed until Nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set
        ->withQueryParams($queryParams);

        $response = $this->app->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $this->assertJsonData($expectedBody, $response);
    }

    /**
     * Request list of all posts when admin is logged in
     * Expected is that all posts have all permissions
     *
     * Fixtures dependency:
     *      UserFixture: one user with id 1 (for session)(better if at least two)
     *      PostFixture: one post (better if at least two)
     *
     * @return void
     */
    public function testPostListAllAction_asAdmin(): void
    {
        // All user fixtures required to insert all post fixtures
        $this->insertFixtures([UserFixture::class, PostFixture::class]);

        // Logged in user 1 role 'admin'
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('post-list')
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that mutation permission is all on all posts
        foreach ($this->getJsonData($response) as $post) {
            self::assertSame(UserNoteData::MUTATION_PERMISSION_ALL, $post['userMutationRight']);
        }
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