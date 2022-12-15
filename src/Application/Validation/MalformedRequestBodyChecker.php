<?php

namespace App\Application\Validation;

/**
 * Validate that request body contains the given keys.
 */
class MalformedRequestBodyChecker
{
    /**
     * Validate that parsed body array contains the given keys.
     *
     * @param array|null $parsedBody null if parsed body is empty
     * @param array $requiredKeys
     * @param array $optionalKeys
     *
     * @return bool
     */
    public function requestBodyHasValidKeys(?array $parsedBody, array $requiredKeys, array $optionalKeys = []): bool
    {
        // Init $amount to be the amount of the required keys meaning
        $amount = count($requiredKeys);
        foreach ($parsedBody ?? [] as $key => $item) {
            // isset cannot be used in this context as it returns false if the key exists but is null, and we don't want
            // to test required fields here, just that the request body syntax is right
            if (in_array($key, $requiredKeys, true)) {
                // Is a required key which is fine, nothing has to done
            } elseif (in_array($key, $optionalKeys, true)) {
                // Add one to amount if optional key is set so that if there is an optional key, $amount goes up meaning
                // a required key cannot be skipped as expected amount won't be the same as parsed body
                $amount++;
            } else {
                // Given array key not in optional nor required
                return false;
            }
        }
        // Check that all required keys are set plus the additional ones if there were some
        return count($parsedBody ?? []) === $amount;
    }
}
