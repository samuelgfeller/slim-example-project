<?php

namespace App\Test\Integration\Actions\Auth;

use App\Test\AppTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\RouteTestTrait;
use Slim\Exception\HttpNotFoundException;

/**
 * Integration test of RegisterAction
 */
class RegisterActionTest extends TestCase
{
    use AppTestTrait;
    use RouteTestTrait;

    public function testAction(): void
    {
        $request = $this->createRequest('GET', $this->urlFor('register-page'));
        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    /**
     * Test invalid link
     * It has to be done only once for Actions without logic
     *
     * @return void
     */
    public function testPageNotFound(): void
    {
        $request = $this->createRequest('GET', '/not-existing-page');

        // Assert with STATUS_NOT_FOUND is not possible because HttpNotFoundException is thrown and stops the test
        $this->expectException(HttpNotFoundException::class);

        $this->app->handle($request);
    }
}
