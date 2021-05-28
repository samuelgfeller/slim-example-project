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
* View templates that are displayed to the user in a browser MUST end with `.html.php`:
  `login-page.html.php`
* Email templates MUST end with `.email.php` (and be in a sub folder "email"):
  `register-confirmation.email.php`
  
## Tests 
* **Folder and files** MUST be in PascalCase format meaning starting with an uppercase and then each word
  is separated by an uppercase too
* **Test classes** names MUST contain the name of the class under test (Action for integration tests) 
  and end with the word Test:  
  Unit test: `UserCreatorTest.php`, Integration test: `RegisterActionTest.php`
* **Fixtures** MUST end with the word Fixture: `UserFixture.php`
* **Provider** MUST end with the word Provider: `UserProvider.php`
* **Helper Traits** MUST end with the name Trait: `AppTestTrait.php`

## Database
* Database and table names all lower case and words separated by underscores
* Database name is the same name as project name but respecting the rule above `slim_example_project`
* Test database name is the same as default database but with the "test" keyword added at the end
  separated by an underscore `slim_example_project_test`