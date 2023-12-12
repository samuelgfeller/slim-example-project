# Slim example project

[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=coverage)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)
[![Lines of Code](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=ncloc)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)

This project showcases an example of a backend and frontend built using the
[Slim](https://www.slimframework.com/) micro-framework.  

The primary goal of this project is to provide a modern codebase with a scalable project structure and 
a range of practical features. These can serve as learning examples or be adapted for developing new 
applications. 

External library dependencies are kept to a minimum to facilitate maintenance and ensure long-term viability.

**Documentation in the [Wiki section](https://github.com/samuelgfeller/slim-example-project/wiki)**.

<details>
  <summary><h2>Installation guide</h2></summary>

In order to install and run this project, you need to have PHP 8, Composer, and a MariaDB or MySQL server 
installed and running on your machine.

### 1. Create project

Navigate to the directory you want to create the project in and run 
the following command, replacing `[project-name]` with the desired name for your project:
```bash
composer create-project samuelgfeller/slim-example-project [project-name]
```
This will create a new directory with the specified name and install all necessary dependencies.

Alternatively, you can use GitHub's 
[Use this template](https://docs.github.com/en/repositories/creating-and-managing-repositories/creating-a-repository-from-a-template)
feature to quickly create a repository with the code of this project. 
Checkout this repository in your preferred IDE before proceeding.

### 2. Set up the database
After opening the project in your IDE, copy the file `config/env/env.example.php` to `config/env/env.php` 
and fill in your database credentials.  

Then, create your database and update the `config/env/env.dev.php` file with the name of your 
database, like this:
```php
$settings['db']['database'] = 'my_database_name';
```
After that, create a separate test database and update the `config/env/env.test.php` file with its
name. The name must contain the word "test" as a safety measure to prevent accidentally truncating 
the development database:
```php
$settings['db']['database'] = 'my_database_name_test';
```

### 3. Run migrations
Open the terminal in the project's root directory and run the following command to create the necessary 
tables for the project:
```bash
composer migrate
```

### 4. Insert data
You can choose to insert only the minimal amount of data required for the app to function, or also 
include some dummy example data.

To insert both minimal and dummy data, run:
```bash
composer seed
```

To insert only the minimal data, run:
```bash
composer seed:minimal
```

### 5. Update GitHub workflows

**Deployment**   
If you are not planning on 
deploying your app at this time, delete or comment out the contents of the 
`.github/workflows/master.yml` file.  
  
To deploy your app, update the `.github/workflows/master.yml` file according to your needs and 
add your server's credentials to GitHub's 
[Actions secrets](https://docs.github.com/en/actions/security-guides/encrypted-secrets).

**Build testing**   
To run the project's tests automatically when pushing, update the 
`.github/workflows/develop.yml` file.   
**Replace the matrix value "test-database" `slim_example_project_test` with the name of 
your test database** as specified in `config/env/env.test.php`.
If you are not using SonarCloud, remove the "SonarCloud Scan" step from the workflow.

### Done!
That's it! Your project should now be fully set up and ready to use.  
You can serve it locally by running `php -S localhost:8080 -t public/` in the project's root 
directory and share it on a version control such as GitHub.

If you notice anything and have a suggestion, please let me know in the 
[feedback issue](https://github.com/samuelgfeller/slim-example-project/issues/1).

</details>


## Features
All features were developed with an effort to ensure maximum user-friendliness. 
It is important to me that the frontend is intuitive, aesthetically pleasing, minimalistic, and functional.

The project is currently designed for non-profit organizations or foundations that require a platform 
to manage the people they assist and maintain a record of communication through notes.

Project components:
* Authentication (login) and authorization (permissions)
* Account verification and password reset via email link and token
* Protection against rapid fire and distributed brute force attacks (time throttling and
  captcha) - [docs](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/security-concept.md)
* Localization - English, German and French
* Flash messages
* Request body and input validation
* Template rendering with native PHP syntax
* An intuitive method for editing values in the browser using "contenteditable"
* Dark theme
* Custom error handler - [docs](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/error-handling.md)
* Integration testing with fixtures and data providers [docs](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/testing/testing-cheatsheet.md)
* Database migrations and seeding [docs](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/cheatsheet.md#database-migrations)

Application components demonstrating real-world features as examples:
* Users with 4 different roles and different permissions
* User management for administrators
* User activity history
* Client creation and mutation with status and attributed user
* Client list filtering by text input and filter chips
* Note creation and mutation for clients with different permissions
* Notes hidden from unauthorised users 
* Dashboard with panels

<details>
  <summary><h3>View demo</h3></summary>

Link: [demo.slim-example-project.samuel-gfeller.ch](https://demo.slim-example-project.samuel-gfeller.ch)  
Usernames: `admin@user.com`, `managing-advisor@user.com`, `advisor@user.com`, `newcomer@user.com`  
Password: 12345678  
The database is reset every hour.

</details>

## Technologies

### Frontend

#### Languages & libraries

Besides HTML and CSS mainly vanilla JS is used to be as "native" as possible utilizing ES6 
JavaScript features extensively.

<details>
  <summary><h4>Template renderer: <a href="https://github.com/slimphp/PHP-View">slimphp/PHP-View</a></h4></summary>

Advantages: 
* Native PHP syntax 
* Very lightweight  
* Easy text translation

Disadvantages:
* The package is much less popular than twig which means that it may be less maintained, has limited documentation, 
and may have limited features (in terms of tools, not syntax)    
* Output strings have to be escaped manually to be protected against XSS attacks   

The fact that the package is less commonly used and potentially less well maintained isn't too concerning 
because it is relatively simple and doesn't involve a lot of code, unlike Twig which creates its own syntax. 
As a result, it requires less maintenance.  

Since it uses native PHP syntax, limited documentation is acceptable, and the "lack" of features can 
be addressed with the help of a middleware (`PhpViewExtensionMiddleware.php`).  

For the XSS protection, strings can be escaped anywhere in the project using the global function `html()`.

</details>
<details>
  <summary><h4>Asset management</h4></summary>

Hard-coding asset paths in templates is not recommended mainly because of the versioning issue. 
Since browsers cache assets to avoid repeated loading, when a JS or CSS file is updated, it is 
important to signal the browser to fetch the latest version by appending a GET parameter to the 
asset link (e.g. `?v=1.0.0`).  

In this project, the version from the config file `default.php` key `['deployment']['version']` is used.

**Include JS and CSS files**  
At the top of each template file, the list of required stylesheets, JS scripts and 
JS modules are added as attributes to the `PhpRenderer` (`$this` in the template file):  
```php
// CSS
$this->addAttribute('css', ['assets/general/page-component/form/form.css',]);  
// JS
$this->addAttribute('js', ['assets/error/error.js',]);  
// JS module
$this->addAttribute('jsModules', ['assets/general/dark-mode/dark-mode.js',]);  
```
They are then added to the HTML in `layout.php` with the current version number.

**JS modules included via import**  
One of the remarkable aspects of ES6 is the `import` statement, as it simplifies the utilization 
of code from other JavaScript files without the need for explicit requirement in the template.   
To address the versioning issue, the script `JsImportCacheBuster.php` 
(called in `PhpViewExtensionMiddleware.php`) traverses through all JavaScript files and updates
the version GET parameter in the import statements.   
So after a version bump in the config file,
it is important to load any page (doesn't matter which one) on the development machine before 
pushing / deploying in order for every JS module to be updated.  
`JsImportCacheBuster.php` is disabled in production since the deployed files are supposed to 
contain the correct versioning information in their import declarations already.

**Other asset paths**  
Image and other paths are directly linked in the templates' tag (e.g. `<img src="">`), and in certain IDEs like PHPStorm, 
the `public/` directory can be marked as Resource Root, enabling automatic path auto-completion.   
The base path is always the public directory.

When an asset is refactored (renamed or moved), the path is automatically updated wherever the 
IDE recognizes the asset path. This functionality works when linking to assets directly in the 
HTML `src` or `href` tag.

</details>

### Backend
The required libraries are carefully chosen to minimize dependencies on external libraries as much as possible.

* HTTP Router & Middleware: [slimphp/Slim](https://github.com/slimphp/Slim)
* HTTP Message Interfaces: [nyholm/psr7](https://github.com/Nyholm/psr7) - [nyholm/psr7-server](https://github.com/Nyholm/psr7-server) - [PSR-7](https://www.php-fig.org/psr/psr-7/)
* Logger: [monolog/monolog](https://github.com/Seldaek/monolog) - [PSR-3](https://www.php-fig.org/psr/psr-3/)
* Dependency Injection Container: [php-di/php-di](https://github.com/PHP-DI/PHP-DI) -
[PSR-11](https://www.php-fig.org/psr/psr-11/)
* Sessions and flash messages: [odan/session](https://github.com/odan/session)
* Database access: [cakephp/database](https://github.com/cakephp/database) - [CakePHP Query Builder](https://book.cakephp.org/4/en/orm/query-builder.html)
* Mailing: [Symfony mailer](https://symfony.com/doc/current/mailer.html)
* Base path: [selective/basepath](https://github.com/selective-php/basepath)

**Dev**
* Generate migration and schema files: [odan/phinx-migrations-generator](https://github.com/odan/phinx-migrations-generator)
* Unit and integration testing: [phpunit/phpunit](https://github.com/sebastianbergmann/phpunit) 
and [selective-php/test-traits](https://github.com/selective-php/test-traits)

<details>
  <summary><h2>Directory structure</h2></summary>

Inspiration for this project were
[odan/slim4-skeleton](https://odan.github.io/slim4-skeleton/) and
[slimphp/Slim-Skeleton](https://github.com/slimphp/Slim-Skeleton), and I did my best to stick to the
[SOLID](https://www.digitalocean.com/community/conceptual-articles/s-o-l-i-d-the-first-five-principles-of-object-oriented-design)
principles.

The folder structure adheres to the [Standard PHP Package Skeleton](https://github.com/php-pds/skeleton).

```
-- config // contains configuration files
-- public
   -- assets // images, videos, stylesheets, scripts, fonts, audio files
-- resources
   -- migrations // database migrations
   -- schema // database table creation schema
   -- seeds // database seed data
-- src
   -- Application // top layer, contains action classes, middlewares, error handler, responder
   -- Domain // includes business logic / service classes
      -- Entity // domain entities
         -- Service // domain service classes
         -- Repository // infrastructure repository classes / database access (vertical slice architecture)
   -- Common // generic helper classes 
-- templates
   -- layout // html layout with nav menu, page structure
   -- // template files (.html.php) for each module 
-- tests
   -- Integration // integration tests
      -- // action class testing which test all layers
   -- Unit // unit tests
      -- // domain service class testing
   -- Fixture // database content to be added as preparation in test db for integration tests
   -- Provider // data provider to run the same test cases with different data
   -- Traits // utility traits (test setup, database connection, helpers)
```
</details>

## Background 
There is a ton of great content on the internet about learning how to write clean and sustainable code,
but I found myself wishing and searching for more than just skeleton projects or general documentations 
when I wanted to learn how to do more complex things within the scope of a potential real-world application.
I never found resources such as an open-source, efficient implementation of all the features surrounding a 
full-sized project.   

One example of the many things I searched online when I wanted to learn how to build a solid, scalable project, 
are complex integration test cases, 
like authorization of actions with different roles in different contexts.   
This project provides not only that but also documentation along with it in the
[testing cheatsheet](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/testing/testing-cheatsheet.md).
It also adresses _what_ should be tested.

## Credits

This project is inspired by Odan's awesome
[knowledge base](https://odan.github.io/), 
[ebook](https://odan.github.io/2022/07/02/slim4-ebook-online.html)
and the [slim4-skeleton](https://odan.github.io/slim4-skeleton/) project.  
Daniel made developing this project so much more fun. Big thanks to him for helping me out and guiding me 
when I was stuck or when I didn't know what a specific best practice was. 
Follow him on [Twitter](https://twitter.com/dopitz).

Special thanks to [JetBrains](https://jb.gg/OpenSource) as well for supporting this project.
PHPStorm is by far [the best PHP IDE](https://www.cloudways.com/blog/top-ide-and-code-editors-php-development/);
I cannot recommend it enough.

## Licence

The MIT Licence (MIT). Please
see the [Licence File](https://github.com/samuelgfeller/slim-example-project/blob/master/LICENCE.txt) 
for more information.
