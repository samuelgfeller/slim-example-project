# Slim example project

Lightweight example project of a backend and frontend done with the [Slim](https://www.slimframework.com/)
micro-framework.
It can be used as a template when creating new projects or just serve as inspiration.

### [Demo](https://demo.slim-example-project.samuel-gfeller.ch/)
Username: admin@admin.com  
Password: 12345678  
The database is reset every 30 minutes.

Please be aware that this app contains a significant amount of content, resulting in a number of 
subjective decisions being made during its development.   
Inspirations were [odan/slim4-skeleton](https://odan.github.io/slim4-skeleton/) 
and [slimphp/Slim-Skeleton](https://github.com/slimphp/Slim-Skeleton) projects, and I did my 
best adhering to the
[SOLID](https://www.digitalocean.com/community/conceptual-articles/s-o-l-i-d-the-first-five-principles-of-object-oriented-design)
principles. 

If you notice any mistakes or have any suggestions, please let me know in the 
[feedback issue](https://github.com/samuelgfeller/slim-example-project/issues/1).

<details>
  <summary><h2>Installation guide</h2></summary>

In order to install and run this project, you need to have PHP, Composer, and a MariaDB or MySQL server 
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
Rename the file `config/env.example.php` to `env.php` and fill in your database credentials.  

Then, create your database and update the `config/env.dev.php` file with the name of your 
database, like this:
```php
$settings['db']['database'] = 'my_database_name';
```
After that, create a separate test database and update the `config/env.test.php` file with its
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
deploying your app at this time, delete or comment out the contents of the `master.yml` file.  
  
To deploy your app, update the `.github/workflows/master.yml` file according to your needs and 
add your server's credentials to GitHub's 
[Actions secrets](https://docs.github.com/en/actions/security-guides/encrypted-secrets).

**Build testing**   
To run the project's tests automatically when pushing, update the 
`.github/workflows/develop.yml` file.   
Replace the matrix value "test-database" `slim_example_project_test` with the name of 
your test database as specified in `config/env.test.php`.
If you are not using SonarCloud, remove the "SonarCloud Scan" step from the workflow.

### Done!
That's it! Your project should now be fully set up and ready to use.  
You can serve it locally by running `php -S localhost:8080 -t public/` in the project's root 
directory and share it on a version control such as GitHub. 

</details>


## Functionalities
It will grow into a usable application for non-profit organizations that need a platform to manage the people they
are helping.  
This project is done in collaboration with the foundation Retter in der Not.

* Authentication (login)
* User management for admins
* Clients creation and mutation with linked status and attributed user
* Notes creation and mutation for clients with different user rights
* Dashboard with panels
* Protection against rapid fire and distributed brute force attacks (time throttling and
  captcha) - [docs](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/security-concept.md)
* [Custom error handler](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/error-handling.md)
* Integration testing with fixtures and data providers 

## Technologies

### Frontend

#### Languages & libraries

Mainly **Vanilla JS** to be as "native" as possible and E6 JavaScript supports a lot

#### Template renderer: **[slimphp/PHP-View](https://github.com/slimphp/PHP-View)**

\+ Native PHP syntax  
\- Much smaller package than twig which means less well maintained, limited documentation, possibly limited features 
(tool-wise not syntax)    
\- I have to take care of XSS attack protection by escaping manually  
\+ Much more lightweight  
\+ Text translation easier  
 

#### Asset management

* Link application specific resources directly in template (which are located under `public/assets/*`)
* With PHPStorm mark directory as Resource Root, which enables paths auto-completion in templates
* Not using any PHP asset library (like [symfony/asset](https://github.com/symfony/asset)
  or [odan/twig-assets](https://github.com/odan/twig-assets))

### Backend
* **HTTP Router & Middleware**: [slimphp/Slim](https://github.com/slimphp/Slim)
* **HTTP Message Interfaces**: [nyholm/psr7](https://github.com/Nyholm/psr7) - [PSR-7](https://www.php-fig.org/psr/psr-7/)
* **Logger**: [Monolog](https://github.com/Seldaek/monolog) - [PSR-3](https://www.php-fig.org/psr/psr-3/)
* **Dependency Injection Container**: [PHP-DI](https://github.com/PHP-DI/PHP-DI) -
[PSR-11](https://www.php-fig.org/psr/psr-11/)
* **Sessions and Flash messages**: [odan/session](https://github.com/odan/session) simple and lightweight
* **Database access**: [CakePHP Query Builder](https://book.cakephp.org/4/en/orm/query-builder.html)
* **Database migration and seeding**: [cakephp/phinx](https://github.com/cakephp/phinx)
* **Generate migration and schema files**: [odan/phinx-migrations-generator](https://github.com/odan/phinx-migrations-generator)
* **Unit and integration testing**: [PHPUnit](https://github.com/sebastianbergmann/phpunit) 
and [selective-php/test-traits](https://github.com/selective-php/test-traits)
* **Mailing**: [Symfony mailer](https://symfony.com/doc/current/mailer.html)

## Structure

```
-- config // contains configuration files
-- public
   -- assets // images, videos, audio files
-- resources
   -- migrations // database migrations
   -- schema // database table creation schema
   -- seeds // database seed data
-- src
   -- Application // top layer, contains action classes
   -- Domain // includes business logic / service classes
   -- Infrastructure // database access / manipulation
   -- Common // generic helper classes 
-- templates
   -- layout // default, "parent" layout of contents
   -- // js, css, html files for each module 
-- tests
   -- Integration // integration tests
      -- // action class testing which test all layers
   -- Unit // unit tests
      -- // domain service class testing
   -- Fixture // database content to be added as preparation in test db for integration tests
   -- Provider // data provider to run same tests with different data
   -- Traits // test utility traits  
```

## Credits

This project is heavily inspired by Odan's awesome 
[knowledge base](https://odan.github.io/), 
[ebook](https://odan.github.io/2022/07/02/slim4-ebook-online.html)
and the [slim4-skeleton](https://odan.github.io/slim4-skeleton/) project.  
Daniel made developing this project so much more fun. Genuinely, thank you. 
Follow him on [Twitter](https://twitter.com/dopitz).

Big thanks to [JetBrains](https://jb.gg/OpenSource) as well for supporting this project.  
PHPStorm is by far [the best PHP IDE](https://www.cloudways.com/blog/top-ide-and-code-editors-php-development/);
I cannot recommend it enough. 

## Licence

The MIT Licence (MIT). Please
see the [Licence File](https://github.com/samuelgfeller/slim-example-project/blob/master/LICENCE.txt) 
for more information.