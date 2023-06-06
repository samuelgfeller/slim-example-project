## Implementation examples

This document functions as a dev journal, documenting some changes made and
the corresponding code modifications.  
It may serve as an inspiration for future feature implementation and bug
fixes. Especially for those that are not familiar with the project code, but I will be using
it as a cheatsheet all the time as well probably.

### Adding a simple new field `theme`

The user can choose a theme that should be saved in the database.  
There are a few places where the code has to be modified and the field added
so that it works like expected.  
The value can only be added by **update**, so we only need to
change one action.  
#### Recap
* `UserTheme.php` create theme enum. 
* `UserSubmitUpdateAction.php` add argument to `malformedRequestBodyChecker()` call.
* `UserValidator.php` add validation line in `validateUserUpdate()`.
* `UserAuthorizationChecker.php` add to the `$grantedUpdateKeys` in `isGrantedToUpdate()`.
* `UserUpdater.php` add to the `if(in_array())` in `updateUser()`.
* `UserData.php` add to the instance variables and constructor.
* `UserFinderRepository.php` add to the instance variable `$fields`.
* `UserUpdateProvider.php` add theme change to `$basicDataChanges` var in `userUpdateAuthorizationCases()`.
* `UserUpdateProvider.php` add theme change to request body and error message to response 
in `invalidUserUpdateCases()`.

#### Implementation

* The field is stored as enum in the database, and it is possible that one day there
will be other themes than just light and dark, so it makes sense to create a php enum:
    ```php
    // Domain/User/Enum/UserTheme.php
    enum UserTheme: string
    {
        case light = 'light';
        case dark = 'dark';
    }
    ```
* In the action class `UserSubmitUpdateAction.php`, `'theme'` has to be added to the
  `malformedRequestBodyChecker` argument list, so it knows `theme` is an accepted value.
* After it passed the request body keys validator, the service function `updateUser()`
is called which first validates the values `validateUserUpdate()`. As it's a backed enum
the function `validateBackedEnum()` can be used:
    ```php
  // UserValidator.php validateUserUpdate()  
  // ...
  if (array_key_exists('theme', $userValues)) {
        $this->validator->validateBackedEnum(
            $userValues['theme'],
            UserTheme::class,
            'theme',
            $validationResult
        );
    }
    ```
* When value validation is done, authorization is tested with `isGrantedToUpdate()`. 
It checks if authenticated user is allowed to change given field. The way it works
is by adding the field name to a `$grantedUpdateKeys` array if the permissions match.  
`theme` has the same authorization rules as the other "general user data" so it's added 
right below the list:
    ```php
    if (array_key_exists('theme', $userDataToUpdate)) {
        $grantedUpdateKeys[] = 'theme';
    }
    ```
* The service function `updateUser()` creates an array of the fields that may be updated
to be certain that only the fields that are designed to be updated actually get changed:
    ```php
    if (in_array($column, ['first_name', '...', 'theme'], true)) {
        $validUpdateData[$column] = $value;
    }
    ```
* Now to be able to read this value and so the app knows that `theme` is now a user value, 
add it to `UserData.php` instance variables and also the constructor.
    ```php
    class UserData implements \JsonSerializable
    {
        /* ... */    
        public ?UserTheme $theme = null;
    
        public function __construct(array $userData = [])
        {
            $this->theme = $userData['theme'] ?? null ? UserTheme::tryFrom($userData['theme']) : null;
        }
    }
    ```
* The file that retrieves the user values from the database is the repository `UserFinderRepository.php`
and it retrieves only the requested values, not automatically all fields. The list of values that
should be selected are in an instance variable of this file `$fields`: 
    ```php
    class UserFinderRepository
    {
        private array $fields = ['id', '...', 'theme',];
        // ...
    }
    ```
#### Testing
* User theme value is an extremely simple, value therefore not much has to be done especially when
there are good existing tests.  
The test itself doesn't even has to be changed, only the data provider that feeds it values
`UserUpdateProvider.php`:
  ```php
  public static function userUpdateAuthorizationCases(): array
  {
      // ...
      $basicDataChanges = ['first_name' => 'NewFirstName', '...', 'theme' => 'dark'];
      // ...
  }
  ```
* There is a validation being made so an invalid value should be tested as well. For this the function  
`invalidUserUpdateCases()` can be extended to also include `'theme' => 'invalid',` in the request body
and `['field' => 'theme', 'message' => 'theme not existing'],` in the response validation errors.