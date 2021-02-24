# Testing

## Unit Testing 
In memory test each component (function) separately as a unit.  
The right input values are provided and if 
the function depends on another class then it either can take it directly from the container or if that class
has to contain specific values, the class is mocked and configured to return a given specific value.

### ActionClass testing
Since action classes do not contain core logic its more meaningful to directly do an integration test 
where its tested that they return the correct values from the database. This avoids a lot of mock creation
which [should be avoided](https://odan.github.io/2020/06/09/slim4-testing.html#mocking).  
More discussion about it on [slim forum](https://discourse.slimframework.com/t/how-to-really-do-a-unit-test-on-a-slim-controller/4618).
 
## Integration testing
Testing how components work together from the request to manipulating the database to returning the correct
values in the right format. 

### Database 
Using [selective/test-traits](https://github.com/selective-php/test-traits) `DatabaseTestTrait.php` 
([documentation](https://odan.github.io/2020/06/09/slim4-testing.html#database-testing))

Tables are created in `setUp()` (located in `AppTestTrait.php`) if test case includes `DatabaseTestTrait` 
like the following:
```php
class UserFindActionTest extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait; // here
    // ...
} 
```

Fixtures are added inside the test functions that need it (UserCreate doesn't need it but 
UserFind does for e.g.): 
```php
    public function testListUsers(): void
    {
        $this->insertFixtures([UserFixture::class]);
        // ...
    }
```
Fixtures passed to `insertFixtures` must be an in an array containing the class names of the wanted
fixtures. Those classes MUST contain two public attributes `$table` and `$records` like this:
```php
class UserFixture
{
    // Table name
    public string $table = 'user';
    
    // Database records in 2d array
    public array $records = [
        [
            'id' => 1,
            'username' => 'admin',
            // ...
        ],
        [
            'id' => 2,
            'username' => 'user',
            // ...
        ],
        // ...
    ];
}
```

**Tables are created only in the first `setUp()`** call since after creating them a constant is defined which is 
valid for the whole runtime that prevents useless drops and creations.   
However, **in each `setUp()` all tables are truncated**. Specific test fixtures can then be added in each 
test function. 
  
### Test database configuration
During testing tables are truncated and created. This is why a second database exists 
`slim_example_project_test`. Here is how `DatabaseTestTrait` knows which database to use.
 * The database name is in the config file `app/env.testing.php`
 * `phpunit.xml` sets the env variable `APP_ENV` to `testing`
 * `app/settings.php` bundles all configuration together and at last checks if `APP_ENV` is set and if 
 it's the case then `env.`[`APP_ENV`-value]`.php` so `env.testing.php` is included which makes that previous config values get 
 overwritten by `env.testing.php`
 
#### Making sure that test db is used
Since I don't have a lot of experience with `phpunit.xml` I don't have 100% trust or if for some reason 
`env.testing.php` is not existing or poorly configured, I don't want to accidentally truncate the wrong 
database tables.  
I added this additional check to the `setUp()` function in `AppTestTrait.php`.
```php
// Check that database name in config contains the the word "test"
if (strpos($container->get('settings')['db']['database'], 'test') === false) {
    throw new UnexpectedValueException('Test database name MUST contain the word "test"');
}
```


## Directory structure
I like [this](https://stackoverflow.com/a/12141610/9013718) approach which separates unit from integration tests.
The directory structure looks like this:
```
└── tests
    ├── Fixture
    │   └── BarFixture.php
    ├── Integration
    │   ├── BarModuleTest.php
    │   └── FooModuleTest.php
    ├── Provider
    │   └── BarProvider.php    
    ├── Unit
        ├── Bar
        │   └── BarAwesomeClassTest.php
        └── Foo
            └── FooAwesomeClassTest.php
    └── AppTestTrait.php      
```

## Visual Component testing
For various reasons it's very hard to automate the testing of visual components, but I think it's very important
to test at least the form validation errors, flash messages etc.

To facilitate this, I'm using a great tool called [insomnia](https://insomnia.rest/). I have my list of saved
requests that I can click on, and the wanted request body is sent with the corresponding HTTP method.

The export of these requests are located in `/resources/insomnia/Insomnia-export_dd-mm-yyyy.json`. This file can simple
be importe in [Insomnia Core](https://insomnia.rest/download).
