<?php

namespace App\Test\Traits;

use Psr\Http\Message\ResponseInterface;
use Selective\TestTrait\Traits\HttpJsonTestTrait;

trait HttpJsonExtensionTestTrait
{
    use HttpJsonTestTrait;

    /**
     * Assert that key => values of given expected JSON are
     * present in the response body.
     * Expected json array doesn't have to contain all the keys
     * returned in response. Only the ones provided are verified.
     *
     * @param array $expectedJson expected json array
     * @param ResponseInterface $response
     *
     * @return void
     */
    protected function assertPartialJsonData(array $expectedJson, ResponseInterface $response): void
    {
        $responseData = $this->getJsonData($response);

        // Assert equals and not same to not fail if the order of the array keys is not correct
        // array_intersect_key removes any keys from the $responseData that are not present in the $expectedJson array
        $this->assertEquals($expectedJson, array_intersect_key($expectedJson, $responseData));
    }
}
