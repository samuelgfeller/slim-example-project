<?php

namespace App\Test\TestCase\Translation;

use App\Test\Trait\AppTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpJsonTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

class TranslateActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
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
        // Testing only response structure and not if strings were translated as localization doesn't work locally
        $this->assertJsonData(['Hello' => 'Hello', 'World' => 'World'], $response);
    }

    /**
     * Test that translate action returns 400 Bad request when query params are missing.
     *
     * @return void
     */
    public function testPasswordResetPageActionTokenMissing(): void
    {
        // Create token with missing query params
        $request = $this->createRequest('GET', $this->urlFor('translate'));
        $response = $this->app->handle($request);
        // Assert 400 Bad request
        self::assertSame(StatusCodeInterface::STATUS_BAD_REQUEST, $response->getStatusCode());
    }
}
