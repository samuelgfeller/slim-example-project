<?php

namespace App\Test\Integration\UtilityTest;

use App\Test\Traits\AppTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

class TranslateActionTest extends TestCase
{
    use AppTestTrait;
    use RouteTestTrait;
    use FixtureTestTrait;
    use HttpJsonTestTrait;

    /**
     * Test that action returns translated strings.
     *
     * @return void
     */
    public function testTranslateAction(): void
    {
        $request = $this->createRequest(
            'GET',
            $this->urlFor('translate'),
        ) // Intentionally not using __() here because locally translations may not work
        ->withQueryParams(['strings' => ['Hello', 'World']]);
        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        // Testing only response structure and not if strings actually were translated as they don't work locally
        $this->assertJsonData(['Hello' => 'Hello', 'World' => 'World',], $response, );
    }

    /**
     * Test that password reset page loads with status code 400 if token is missing.
     *
     * @return void
     */
    public function testPasswordResetPageActionTokenMissing(): void
    {
        // Create token with missing token
        $request = $this->createRequest('GET', $this->urlFor('translate'));
        $response = $this->app->handle($request);
        // Assert 400 Bad request
        self::assertSame(StatusCodeInterface::STATUS_BAD_REQUEST, $response->getStatusCode());
    }
}
