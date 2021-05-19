<?php

namespace App\Test\Integration\Actions\Post;

use App\Test\AppTestTrait;
use App\Test\Fixture\PostFixture;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;
use Slim\Exception\HttpBadRequestException;

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

    /**
     * User update process with valid data
     *
     * @return void
     */
    public function testPostListAction(): void
    {
        $this->insertFixtures([UserFixture::class]);
        $this->insertFixtures([PostFixture::class]);

        // Simulate logged in user with id 1
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $request = $this->createJsonRequest(
            'GET',
            // Request to change user with id 1
            $this->urlFor('post-list-all')
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $expected = [
            // id is string as CakePHP Database returns always strings: https://stackoverflow.com/a/11913488/9013718
            'id' => '1',
            'name' => 'Admin Example edited',
            'email' => 'edited_admin@example.com',
            // Not password since the hash value always changes, it's asserted later
        ];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
//        $this->assertTableRow($expected, 'user', 1, array_keys($expected));

        // Assert that field "id" of row with id 1 equals to "1" (CakePHP returns always strings)
//        $this->assertTableRowValue('1', 'user', 1, 'id');
    }

}