<?php

use Selective\TestTrait\Traits\RouteTestTrait;

class DebuggingTest extends \PHPUnit\Framework\TestCase
{
    use \App\Test\Traits\AppTestTrait;
    use RouteTestTrait;
    use \Selective\TestTrait\Traits\HttpTestTrait;
    use \Selective\TestTrait\Traits\DatabaseTestTrait;

    public function testLogin()
    {
        // Insert 2 users into db
        // $this->insertFixtures([\App\Test\Fixture\UserFixture::class]);
        $request = $this->createFormRequest(
            'POST',
            $this->urlFor('login-submit'),
            // Same keys than HTML form
            [
                'email' => 'contact@samuel-gfeller.ch',
                'password' => '123',
            ]
        );
        // $response = $this->app->handle($request);

        self::assertTrue(true);
    }
}
