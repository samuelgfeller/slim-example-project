# Naming convention

## Root-level directories
From [php-pds/skeleton](https://github.com/php-pds/skeleton)

If a package has a root-level directory for ... |	... then it MUST be named:
--- | ---
command-line executables	| `bin/`
configuration files	| `config/`
documentation files	| `docs/`
web server files	| `public/`
other resource files	| `resources/`
PHP source code	| `src/`
test code	| `tests/`

## General files and folders
E.g. project name, config files in `config/`, doc files in `docs/`, log files in `logs/`, resources in 
`resources/`, assets in `public/assets/`
* All filenames SHOULD be all lowercase `file.ext`
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
* **Data Transfer Objects (DTO)** MUST end with Data: `UserData.php` because they are just a format of 
dealing with data.
  
## Templates
* Follow [general files and folders](#General-files-and-folders) rules
* View templates that are displayed to the user are in the module sub folder and just end
  with `.php`: e.g.  `login-page.php`.   
  *Note*: previous `.html` is useless as emails and other views are in their respective sub-folder.
  It is very clear in the path name which type it is and there is no autocompletion anyway as it's 
  written in a string.
* Email templates, PDF and other views MUST be in a respective sub folder 
  (`email/email-template.php` or `pdf/pdf-template.php`)   
  
## Tests 
* **Folder and files** MUST be in PascalCase format meaning starting with an uppercase and then each word
  is separated by an uppercase too
* **Test classes** names MUST contain the name of the class under test (Action for integration tests) 
  and end with the word Test:  
  Unit test: `UserCreatorTest.php`, Integration test: `RegisterActionTest.php`
* **Fixtures** MUST end with the word Fixture: `UserFixture.php`
* **Provider** MUST end with the word Provider: `UserProvider.php`
* **Helper Traits** MUST end with the name Trait: `AppTestTrait.php`

### Test functions
* **Unit test functions** MUST be named as follows: `testFunctionNameUnderTest_additionalInfo`.  
  Starting with
the word "test" followed by the name of the function which is being tested in camel case
  and if a function is tested multiple times differently the test specificity should be in camel case
  separated by an underscore (to differentiate easier and prevent unclear long test function names)
  
* **Integration test functions** MUST start with test as well but then describe the use case which
is being tested and additional info can be added in camel case separated by an underscore 
  e.g. `testUserRegistration_existingActiveUser`
  
### Providers
I differentiate providers into two types. They have to be in a sub folder "Provider" at the root of 
the `/tests` folder. As the same providers are used by both unit and integration tests, they can't 
be inside the test case bundle.  
Each are in folders with the bundle name after "Provider".

#### Data Providers
Provide different only the data to run for a specific test.   
E.g. For a test asserting that a validationException occurs with different invalid data 
(name too long, password too short, not given email address etc.)  

Data provider MUST end with `DataProvider.php` and be in the folder of their module name which is in
the folder "Provider".

#### Case Providers
They contain both the input data but also the expected value for
the assertion. They are here to make more generic test cases which are like a formula 
and being able to feed them with many different data and expected values.  
E.g. Asserting that validation exception occurs with a specific error message and behaviour.

Case provider MUST end with `CaseProvider.php` and be in the folder of their module name which is in
the folder "Provider".

### Fixtures
Unlike providers, fixtures are only used in integration tests. Therefore, they can be stored inside
the integration test case bundle folder in a "Fixture" sub folder 
(e.g. `tests\Integration\User\Fixture`).  
Fixtures MUST end with `Fixture.php`.


## Database
* Database and table names MUST be all lower case and words separated by underscores
* Database SHOULD have the same name as the project following the rule above: `slim_example_project`
* Test database name is the same as default database but with the "test" keyword added at the end
  separated by an underscore `slim_example_project_test`