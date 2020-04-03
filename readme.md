# Slim api template
Lightweight example project of an API with the SLIM 4 Micro-Framework.  
  
The functionalities include:
* Authentication with JWT-Token (registration and login)
* User mangement for a User with 'admin' role
* Post creation for all user
* Own post management (edit and delete)
* All user can see all posts 


## Frontend for testing
https://github.com/samuelgfeller/frontend-example

  
  
### settings.php
```php
<?php
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => true, // Should be set to false in production
            'logger' => [
                'name' => 'event-log',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                'level' => Logger::DEBUG,
            ],
            'db' => [
                'host' => 'localhost',
                'database' => 'slim-api-example',
                'user' => 'root',
                'pass' => '',
            ],
        ],
    ]);
};
```

### Validation
**!! The below has changed and is not valid anymore !!**
The user inputs are intercepted in the controller. They are then passed to a validation class 
which validates the data and returns an instance of `ValidationResult` which returns an array 
with the errors with information about what failed in which field. The method 
`fails()` checks if the error array is empty and returns a boolean which is `true` if the error 
array actually contains errors and therefore the validation failed. If this is the case, a 
response is sent to the client with the error status and the various 
errors as JSON.
  
Here is how I check if the required values passed in the request are set and have the right name (key). 
Lets say we expect the required elements `name`,`email`,`password`,`password2`   
  
Get elements from request as array and give this data to the corresponding validation class which 
will return the result
```php
public function register(Request $request, Response $response): Response
{
    $parsedBody = $request->getParsedBody();
    $validationResult = $this->userValidation->validateUserRegistration($parsedBody);
    ...
}
 ```

This method creates a validation result instance. At the beginning the `if isset` checks if the
names are present in the array. If they are not the data is not even further validated and
the function `setIsBadRequest` is called which will tell `ValidationResult` that the status is 
`400 bad request`.   
If the names are all set the validation can proceed. At the end a new array 
is created with the validated data. This allows different names and then use of these names in a 
different key name in the application. 
```php
public function validateUserRegistration($userData): ValidationResult
{
    $validationResult = new ValidationResult('There is something in the registration data which couldn\'t be validated');
    if (isset($userData['email'], $userData['name'],$userData['password'],$userData['password2'])) {

        $this->validateName($userData['email'], $validationResult);

        $this->validatePasswords([$userData['password'], $userData['password2']], $validationResult);

        // Create array with validated user input. Because a new array is created, the keys will always be the same and we don't have to worry if
        // we want to change the requested name. Modification would only occur here and the application would still be able to use the same keys
        $validatedData = [
            'name' => $userData['name'],
            'email' => filter_var($userData['email'], FILTER_VALIDATE_EMAIL),
            'password' => $userData['password'],
            'password2' => $userData['password2'],
        ];
        $validationResult->setValidatedData($validatedData);

        return $validationResult;
    }
    $validationResult->setIsBadRequest(true);
    return $validationResult;
}
```
Back in the `register` function we have the validation result and can check if the validation failed.  
If it's the case a response is returned to the client with the corresponding error message(s) and status
code. `422` is the [default code for a validation error](https://stackoverflow.com/a/3291292/9013718) and 
`400` for a bad request if the body is not set well. 
```php
$validationResult = $this->userValidation->validateUserRegistration($parsedBody);
if ($validationResult->fails()) {
    $responseData = [
        'status' => 'error',
        'message' => 'Validation error',
        'validation' => $validationResult->toArray(),
    ];

    return $this->respondWithJson($response, $responseData, $validationResult->getStatusCode());
}

$userData = $validationResult->getValidatedData();

// code here
```


### Returning error messages
Backend SHOULD return `"message":"errorMsg"` which MUST be a JSON response so the frontend has information 
about the reason of the fail.   
The javascript function `handleFail(xhr)` checks for `xhr.responseJSON.message` **if it finds it** so not 
mandatory but is be good in some situations for clarity and user experience.









