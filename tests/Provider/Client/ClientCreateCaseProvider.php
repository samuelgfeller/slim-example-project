<?php

namespace App\Test\Provider\Client;

class ClientCreateCaseProvider
{

    /**
     * Provide malformed request body for client creation
     *
     * @return array[]
     */
    public function malformedRequestBody(): array
    {
        return [
            [
                // If any of the list except client_message is missing it's a bad request
                'missing_first_name' => [
                    'last_name' => 'value',
                    'birthdate' => 'value',
                    'location' => 'value',
                    'phone' => 'value',
                    'email' => 'value',
                    'sex' => 'value',
                    'client_message' => 'value',
                    'user_id' => 'value',
                    'client_status_id' => 'value',
                ],
                'key_too_much_without_client_message' => [
                    'first_name' => 'value',
                    'last_name' => 'value',
                    'birthdate' => 'value',
                    'location' => 'value',
                    'phone' => 'value',
                    'email' => 'value',
                    'sex' => 'value',
                    // 'client_message' => 'value',
                    'user_id' => 'value',
                    'client_status_id' => 'value',
                    'key_too_much' => 'value',
                ],
            ]
        ];
    }
}