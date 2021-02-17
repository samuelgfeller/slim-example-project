<?php

namespace App\Test\Integration\Application\Actions\Auth;

use App\Test\AppTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

class RegisterSubmitActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;

    /**
     * Test.
     *
     * @return void
     */
    public function testCreateUser(): void
    {
        $request = $this->createFormRequest('POST', $this->urlFor('register-submit'),
            // Same keys than HTML form
            [
                'name' => 'Admin Example',
                'email' => 'admin@example.com',
                'password' => '123',
                'password2' => '123',
                'role' => 'admin',
            ]
        );

        $response = $this->app->handle($request);

        // Assert: 302 Found (redirect)
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Check database
        $this->assertTableRowCount(1, 'user');

        $expected = [
            // CakePHP Database returns always strings: https://stackoverflow.com/a/11913488/9013718
            'id' => '1',
            'name' => 'Admin Example',
            'email' => 'admin@example.com',
            'role' => 'admin',
            // Not password since the hash value always changes
        ];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
        $this->assertTableRow($expected, 'user', 1, array_keys($expected));

        // Assert that field "id" of row with id 1 equals to "1" (CakePHP returns always strings)
        $this->assertTableRowValue('1', 'user', 1, 'id');

        // Password
        $password = $this->getTableRowById('user', 1)['password_hash'];
        // Assert that password_hash starts with the beginning of a BCRYPT hash
        self::assertStringStartsWith(
            '$2y$10$',
            $password,
            'password_hash not starting with beginning of bcrypt hash'
        );
    }

}
