<?php

namespace App\Test\Integration\Actions\Authentication;

use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\UserFixture;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;

class LoginSubmitActionTest extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait;

    public function testTest()
    {
        $this->insertFixtures([UserFixture::class]);

        self::assertEquals(true, true);
    }
}
