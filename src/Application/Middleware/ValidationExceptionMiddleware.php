<?php

namespace App\Application\Middleware;

use App\Application\Responder\Responder;
use App\Domain\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationExceptionMiddleware implements MiddlewareInterface
{

    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $validationException) {
            // Create response (status code and header are added later)
            $response = $this->responder->createResponse();
            // For an added abstraction layer, in order to not be dependent on validation library's error output format,
            // transform cakephp validation errors into the format that is used in the frontend
            $validationErrors = $this->transformCakephpValidationErrorsToOutputFormat(
                $validationException->validationErrors
            );
            $responseData = [
                'status' => 'error',
                'message' => $validationException->getMessage(),
                'data' => ['errors' => $validationErrors],
            ];
            return $this->responder->respondWithJson($response, $responseData, 422);
        }
    }

    /**
     * Transform the validation error output from the library to array that is used by the frontend.
     * The changes are tiny but the main purpose is to add an abstraction layer in case the validation
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
     * Previously the output format was like this:
     * [
     *    0 => [
     *      'field' => 'field_name',
     *      'message' => 'Validation error message for that field',
     *    ],
     *    // ... and so on
     * ]
     * But this makes it unnecessarily harder to test as the order of the array elements is not
     * guaranteed in the response.
     *
     * @param array $validationErrors The cakephp validation errors
     * @return array The transformed result in the format documented above.
     */
    private function transformCakephpValidationErrorsToOutputFormat(array $validationErrors): array
    {
        $validationErrorsForOutput = [];
        foreach ($validationErrors as $fieldName => $fieldErrors) {
            // There may be the case that there are multiple error messages for a single field.
            foreach ($fieldErrors as $infringedRuleName => $infringedRuleMessage) {
                // Output is basically the same except we don't need the infringed rule name as key
                $validationErrorsForOutput[$fieldName][] = $infringedRuleMessage;
            }
        }

        return $validationErrorsForOutput;
    }
}
