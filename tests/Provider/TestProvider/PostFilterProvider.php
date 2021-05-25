<?php


namespace App\Test\Provider\TestProvider;


class PostFilterProvider
{
    /**
     * Return every filter combination for integration testing
     *
     * @return array GET params with invalid filter values and record filter
     */
    public function provideValidFilter(): array
    {
        return [
          [
              'queryParams' => ['user' => 1],
              'recordFilter' => ['user_id' => 1]
          ]
        ];
    }

    /**
     * Return invalid filters
     *
     * @return array GET params with invalid filter values and expected return body
     */
    public function provideInvalidFilter(): array
    {
        return [
          [
              'queryParams' => ['user' => 'invalid_value'], // Provide letters instead of numeric
              'expectedReturnBody' => [
                  'status' => 'error',
                  'message' => 'Filter "user" is not numeric.',
              ],
          ]
        ];
    }
}