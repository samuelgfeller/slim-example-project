# Slim example project [work in progress]

Lightweight example project of a backend and frontend done with the [Slim](https://www.slimframework.com/)
micro-framework.   
It can be used as a template when creating new projects or just serve as inspiration.

If you want to learn how to develop properly however, I strongly recommend that you check out Daniel Opitz's
resources.  
This whole project is greatly inspired by Daniel's fabulous [blog articles](https://odan.github.io/)
, [ebook](https://odan.github.io/2022/07/02/slim4-ebook-online.html)
and the [slim4-skeleton](https://odan.github.io/slim4-skeleton/) project.

## Functionalities include:

It will grow into a usable application for non-profit organizations that need a platform to the manage the people they
are helping.  
This project is done in collaboration with the organization [Retter in der Not](https://www.retter-in-der-not.org).

* Authentication (login)
* User management for admins
* Clients creation and mutation with linked status and user
* Notes creation and mutation for clients with different user rights
* Protection against rapid fire and distributed brute force attacks (time throttling and
  captcha) - [docs](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/security-concept.md)
* [Custom error handler](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/error-handling.md)
* 

## Technologies

### Frontend

#### Languages & libraries

* Mainly **Vanilla JS** to be as "native" as possible and since E6 JavaScript supports a lot
* Avoiding the use of jQuery but rather add the needed missing components specifically one by one and then bundle it
  into 1 static file

#### Template renderer

* Moved from twig to **[slimphp/PHP-View](https://github.com/slimphp/PHP-View)**
    * \+ Native PHP syntax
    * \+ Much more lightweight
    * \+ Text translation easier
    * \- Much smaller than twig which means less well maintained, limited documentation, possibly limited features (
      tool-wise not syntax)
    * \- I have to take care of XSS attack protection by escaping manually (easily solvable)

#### Asset management

After talking with [Odan](http://disq.us/p/2dlx8ql) (comment section) I will do the following:

* Link application specific resources directly in template (which are located under `public/assets/*`)
* Not using any PHP asset library (like [symfony/asset](https://github.com/symfony/asset)
  or [odan/twig-assets](https://github.com/odan/twig-assets))
* If during the development of a larger project many libraries are being used, I will
    1. Install webpack and use it to download and compile/bundle my external dependencies (like jQuery, Bootstrap,
       etc..) into a single JS file.
    1. Link this static file in my global layout template(s). For this I don't need an asset function.
    1. And then I can update my external dependencies with `npx webpack --mode=production`
       But I think most smaller projects won't need enough to justify it. Thats why I left it out in this
       example-project

### Backend
* **HTTP Router & Middleware**: [slimphp/Slim](https://github.com/slimphp/Slim)
* **HTTP Message Interfaces**: [nyholm/psr7](https://github.com/Nyholm/psr7) - [PSR-7](https://www.php-fig.org/psr/psr-7/)
* **Logger**: [Monolog](https://github.com/Seldaek/monolog) - [PSR-3](https://www.php-fig.org/psr/psr-3/)
* **Dependency Injection Container**: [PHP-DI](https://github.com/PHP-DI/PHP-DI) -
[PSR-11](https://www.php-fig.org/psr/psr-11/)
* **Sessions and Flash messages**: [odan/session](https://github.com/odan/session) simple and lightweight
* **Database access**: [CakePHP Query Builder](https://book.cakephp.org/4/en/orm/query-builder.html)
* **Unit and integration testing**: [PHPUnit](https://github.com/sebastianbergmann/phpunit) 
and [selective-php/test-traits](https://github.com/selective-php/test-traits)
* **Mailing**: [Symfony mailer](https://symfony.com/doc/current/mailer.html)

## Structure

```
-- config // contains configuration files
-- public
   -- assets // images, videos, audio files
-- resources
   -- schema // database table creation schema
-- src
   -- Application // top layer, contains action classes
   -- Domain // includes business logic / service classes
   -- Infrastrucutre // database access / manipulation
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

## Licence

The MIT Licence (MIT). Please
see [Licence File](https://github.com/samuelgfeller/slim-example-project/blob/master/LICENCE.txt) for more information.

### Credits

Daniel made developing this project so much more fun. Thanks to his [knowledgebase](https://odan.github.io/),
his [slim4-skeleton](https://odan.github.io/slim4-skeleton/) project and the answers to my multiple questions he
made me save countless hours. It's worth following him on [Twitter](https://twitter.com/dopitz).

Big thanks to [JetBrains](https://jb.gg/OpenSource) as well for supporting this project.  
PHPStorm is by far [the best PHP IDE](https://www.cloudways.com/blog/top-ide-and-code-editors-php-development/);
I cannot recommend it enough. 
