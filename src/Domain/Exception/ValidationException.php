<?php

namespace App\Domain\Exception;

use RuntimeException;

class ValidationException extends RuntimeException
{
    public readonly array $validationErrors;

    public function __construct(array $validationErrors, string $message = 'Validation error')
    {
        parent::__construct($message);

        $this->validationErrors = $this->transformCakephpValidationErrorsToOutputFormat($validationErrors);
    }

    /**
     * Transform the validation error output from the library to array that is used by the frontend.
     * The changes are tiny, but the main purpose is to add an abstraction layer in case the validation
     * library changes its error output format in the future so that only this function has to be
     * changed instead of the frontend.
     *
     * In cakephp/validation 5 the error array is output in the following format:
     * $cakeValidationErrors = [
     *    'field_name' => [
     *        'validation_rule_name' => 'Validation error message for that field',
     *        'other_validation_rule_name' => 'Another validation error message for that field',
     *    ],
     *    'email' => [
     *        '_required' => 'This field is required',
     *    ],
     *    'first_name' => [
     *        'minLength' => 'Minimum length is 3',
     *    ],
     * ]
     *
     * This function transforms this into the format that is used by the frontend
     * (which is roughly the same except we don't need the infringed rule name as key):
     * $outputValidationErrors = [
     *    'field_name' => [
     *        0 => 'Validation error message for that field',
     *        1 => 'Another validation error message for that field',
     *    ],
     *    'email' => [
     *        0 => 'This field is required',
     *    ],
     *    'first_name' => [
     *        0 => 'Minimum length is 3',
     *    ],
     * ]
     *
     * @param array $validationErrors The cakephp validation errors
     *
     * @return array the transformed result in the format documented above
     */
    private function transformCakephpValidationErrorsToOutputFormat(array $validationErrors): array
    {
        $validationErrorsForOutput = [];
        foreach ($validationErrors as $fieldName => $fieldErrors) {
            // There may be cases with multiple error messages for a single field.
            foreach ($fieldErrors as $infringedRuleName => $infringedRuleMessage) {
                // Output is basically the same except without the rule name as a key.
                $validationErrorsForOutput[$fieldName][] = $infringedRuleMessage;
            }
        }

        return $validationErrorsForOutput;
    }
}
