<?php


namespace App\Test\Integration\Post;


use _PHPStan_76800bfb5\Nette\Schema\ValidationException;
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
use Slim\Exception\HttpBadRequestException;

class PostCreateActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;

    /**
     * Post creation with valid data
     * Test has to behave like frontend
     *
     * @return void
     */
    public function testPostCreateAction(): void
    {
        // Insert logged in user
        $userRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $userRow);

        // Simulate logged-in user with id 1
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $postMessage = 'This is a normal test post.';
        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('post-submit-create'),
            // Request body
            ['message' => $postMessage]
        );

        $response = $this->app->handle($request);

        // Assert: 201 Created
        self::assertSame(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());

        $expected = [
            // id is string as CakePHP Database returns always strings: https://stackoverflow.com/a/11913488/9013718
            'id' => '1',
            'message' => $postMessage,
        ];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
        $this->assertTableRow($expected, 'post', 1, array_keys($expected));
    }

    /**
     * Request body containing wrong keys or no key
     *
     * @dataProvider \App\Test\Provider\Post\PostCaseProvider::providePostCreateMalformedBody()
     *
     * @return void
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function testPostCreateAction_malformedBody($requestBodyArr): void
    {
        // Insert logged in user
        $userRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $userRow);

        // Simulate logged-in user with id 1
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('post-submit-create'),
            // Request body
            $requestBodyArr
        );

        // Assert that bad request exception is being thrown
        $this->expectException(HttpBadRequestException::class);

        $response = $this->app->handle($request);
    }

    /**
     * Test post creation with invalid data. Validation exception expected.
     *
     * @dataProvider \App\Test\Provider\Post\PostCaseProvider::providePostCreateInvalidData()
     *
     * @return void
     */
    public function testPostCreateAction_invalid(array $requestBodyArr, array $validationErrorData): void
    {
        // Insert logged in user
        $userRow = (new UserFixture())->records[0];
        $this->insertFixture('user', $userRow);

        // Simulate logged-in user with id 1
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('post-submit-create'),
            // Request body
            $requestBodyArr
        );

        $response = $this->app->handle($request);

        // Assert HTTP status code to 422 Unprocessable Entity
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // Assert that response is json
        $this->assertJsonContentType($response);

        // Assert exact validation error string (response standard: SLE-134)
        $this->assertJsonData($validationErrorData, $response);
    }

}
