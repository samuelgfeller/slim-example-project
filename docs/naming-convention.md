# Naming convention

## Root-level directories
From [php-pds/skeleton](https://github.com/php-pds/skeleton)

| If a package has a root-level directory for ... |	... then it MUST be named: |
|-------------------------------------------------|:---:|
| command-line executables	                       | `bin/` |
| configuration files	                            | `config/` |
| documentation files	                            | `docs/` |
| web server files	                               | `public/` |
| other resource files	                           | `resources/` |
| PHP source code	                                | `src/` |
| test code	                                      | `tests/` |

## General files and folders
E.g. project name, config files in `config/`, doc files in `docs/`, log files in `logs/`, resources in 
`resources/`, assets in `public/assets/`
* All filenames SHOULD be lowercase `file.ext`
* All words of filenames SHOULD be separated by hyphens `file-name.ext`
* It SHOULD be tried to avoid multi-worded folders but if there are, they MUST be all lowercase 
and separated by hyphens `folder-name`

## Backend project files and folders in `src/`
* **Folder and files** MUST be in PascalCase format meaning starting with an uppercase and then each word 
is separated by an uppercase too
* **Action classes** MUST end with the word Action: `LoginAction.php`
* **Service classes** are do-er classes and MUST be agent names and NOT contain the word "Service": 
`UserCreator.php`
* **Repository classes** MUST end with the word Repository and be named after their according
  service class if there is any: `UserCreatorRepository.php`
* **Exception classes** MUST end with the word Exception: `InvalidCredentialsException.php`
* **Middlewares** MUST end with the word Middleware: `UserAuthenticationMiddleware.php`
* **Data Transfer Objects (DTO)** MUST end with Data: `UserData.php` and SHOULD implement `\JsonSerializable`
and have a `jsonSerialize()` function when needed (e.g. to return the correct `\DateTime` format to the view).   
There are three kinds of DTOs: 
  * Entity-like which SHOULD be named after the database table MUST contain attributes with the same name
    as the database column names in camelCase.
  * Result-values which contain aggregate data from different tables and are tailored to the 
    view with the needed values in the correct format. These classes MUST end with ResultData: `ClientResultData`.
  * Collection of Data objects with optional additional attributes. MUST end with ResultDataCollection: 
    `ClientResultDataCollection`.
  
## Actions
1. **Use Case Specific**: The action name should clearly indicate the use case it handles.
The name of the resource should be the first word and in singular form (first word to be able to
find it better on a project wide search).  
For example, if an action handles updating a client, it could be named `ClientUpdateAction.php`.
2. **Request Type Specific**: The action name should also indicate the type of request it handles.    
For example, for fetch requests, `Fetch` could be used in the action name like `ClientFetchAction.php`.   
For actions that display a page, the word `Page` should be in the action name like `LoginPageAction.php`.   
Alternatively the word "Show" can also be used as it makes clear that a page is rendered `ShowUserProfileAction.php`. 
4. **Suffix with "Action"**: `Action` at the end of the action names indicates that 
the class is an action.
5. **Prefix with "Api"**: Only for Api requests add `Api` at the beginning of the action name
to indicate that the request is made from another application via this interface.
5. **Folder Structure**: Actions are organized into "Page" and "Ajax" folders based on whether they 
handle page requests or Ajax requests.

Based on these guidelines, here are some examples for different types of requests:

- Show page actions: `LoginPageAction.php`, `UserProfilePageAction.php`, `ShowUserProfileAction.php`
- Fetch collection of data: `ClientFetchListAction.php`, `NoteFetchListAction.php`
- Read, get specific set of data: `ClientReadAction.php`, `UserReadAction.php`
- Submit/Process requests: `LoginSubmitAction.php`, `PasswordForgottenSubmitEmailAction.php`, 
`NewPasswordResetSubmitAction.php`, `AccountUnlockProcessAction.php`
- Create requests: `ClientCreateAction.php`, `UserCreateAction.php`
- Update requests: `ClientUpdateAction.php`, `NoteUpdateAction.php`
- Delete requests: `ClientDeleteAction.php`, `NoteDeleteAction.php`
- Api requests: `ApiClientCreateAction.php`,

## Templates
* Follow [general files and folders](#General-files-and-folders) rules
* View templates that are displayed to the user are in the module sub folder and must end
  with `.html.php`: e.g.  `login-page.html.php`.
* Email templates, PDF and other views MUST be in a respective sub folder 
  (`email/email-template.php` or `pdf/pdf-template.php`)   

### HTML elements
* IDs and class names words MUST be separated by a hyphen: `class-name`
* Form `name` attributes words MUST be separated by an underscore and MUST be the same as the database
column name: `name="input_name"`.  
The reason is that `Data` classes are populated via constructor from the parsed request body.
  
## Tests 
* **Folder and files** MUST be in PascalCase format meaning starting with an uppercase and then each word
  is separated by an uppercase too
* **Test classes** names MUST contain the name of the class under test (Action for integration tests) 
  and end with the word Test:  
  Unit test: `ClientCreatorTest.php`, Integration test: `ClientCreatorActionTest.php`
* **Fixtures** MUST end with the word Fixture: `ClientFixture.php`
* **Provider** MUST end with the word Provider: `ClientProvider.php`
* **Helper Traits** MUST end with the name Trait: `AppTestTrait.php`

### Test functions
* **Unit test functions** MUST be named as follows: `testFunctionNameUnderTest_additionalInfo`.  
  Starting with
the word "test" followed by the name of the function which is being tested in camel case
  and if a function is tested multiple times differently the test specificity should be in camel case
  separated by an underscore (to differentiate easier and prevent unclear long test function names)
  
* **Integration test functions** MUST start with test as well but then describe the use case which
is being tested and additional info can be added in camel case separated by an underscore 
  e.g. `testClientCreation_authorization`
  
### Providers
They have to be in a sub folder "Provider" at the root of 
the `/tests` folder. The same providers may be used by both unit and integration tests.  
Each are in folders with the bundle name in the folder "Provider".

Previously I differentiated two types of providers that had different class names: 

#### Data Providers
Provide different only the data to run for a specific test.   
E.g. For a test asserting that a validationException occurs with different invalid data 
(name too long, password too short, not given email address etc.)  

#### Case Providers
They contain both the input data but also the expected value for
the assertion. They are here to make more generic test cases which are like a formula 
and being able to feed them with different data and expected values.  
E.g. Asserting that validation exception occurs with a specific error message and behaviour or different 
authorization cases.

But it doesn't make that much of a difference and I don't want to add useless complexity so all providers are in 
classes that MUST end with "Provider": `ClientCreateProvider` that can both contain data and case providers.

### Fixtures
Unlike providers, fixtures are only used in integration tests. Therefore, they can be stored inside
the integration test case bundle folder in a "Fixture" sub folder 
(e.g. `tests\Integration\User\Fixture`).  
Fixtures MUST end with `Fixture.php` and implement `FixtureInterface.php`.

## Database
* Database and table names MUST be all lower case and words separated by underscores
* Database SHOULD have the same name as the project following the rule above: `slim_example_project`
* Test database name is the same as default database but with the "test" keyword added at the end
  separated by an underscore `slim_example_project_test`