<?php


namespace App\Test\Provider\Post;


class PostFilterUnitCaseProvider
{
    /**
     * Return every filter combination for unit testing
     *
     * @return array GET params with invalid filter values and record filter
     */
    public function provideFilter_user(): array
    {
        return [
            ['filterParams' => ['user' => 1],],
            ['filterParams' => ['user' => 'invalid'],],
            ['filterParams' => ['user' => ''],],
            // Expandable with more filter
        ];
    }
}