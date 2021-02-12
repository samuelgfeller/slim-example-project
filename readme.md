# Slim example project [Work in progress]
Lightweight example project of a backend and frontend done with the SLIM 4 Micro-Framework.   
This can be used as a base when creating new projects or just serve as inspiration.    
I make it mainly for myself since I'm still junior and don't have the pretension to release something that I consider as a go to example for anyone.  
   
If you want to learn how to develop properly, I strongly suggest to head over the resources from Daniel Opitz.
In fact this whole project is greatly inspired by Daniel's fabulous [Blog articles](https://odan.github.io/) and his [slim4-skeleton](https://odan.github.io/slim4-skeleton/) project.
  
## Functionalities include:
* Authentication (registration and login)
* User management for a User with 'admin' role
* Post creation for all user
* Own post management (edit and delete)
* All user can see all posts 

## Technologies
### Frontend
#### Languages & libraries
* Mainly **Vanilla JS** to be as "native" as possible and since E6 JavaScript supports a lot 
* Avoiding the use of jQuery but rather add the needed missing components specifically one by one and then bundle it into 1 static file 
#### Template renderer
* Moved from twig to **[slimphp/PHP-View](https://github.com/slimphp/PHP-View)** 
  * \+ Native PHP syntax
  * \+ Text translation easier
  * \+ Much more lightweight 
  * \- I have to take care of XSS attack protection by escaping manually
#### Asset management 
After talking with [Odan](http://disq.us/p/2dlx8ql) (comment section) I will do the following:
* Link application specific resources directly in template (which are located under `public/assets/*`)
* Not using any PHP asset library (like [symfony/asset](https://github.com/symfony/asset) or [odan/twig-assets](https://github.com/odan/twig-assets))
* If during the development of a larger project many libraries are being used, I will
    1. Install webpack and use it to download and compile/bundle my external dependencies (like jQuery, Bootstrap, etc..) into a single JS file.
    1. Link this static file in my global layout template(s). For this I don't need an asset function.
    1. And then I can update my external dependencies with `npx webpack --mode=production`
  But I think most smaller projects won't need enough to justify it. Thats why I left it out in this example-project 
### Backend
* **Framework**: [slimphp/Slim](https://github.com/slimphp/Slim)
* [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) logger [Monolog](https://github.com/Seldaek/monolog)
* PSR-11 **Dependency injection container**: [PHP-DI](https://github.com/PHP-DI/PHP-DI)
* **Sessions and Flash messages**: For now I use the [odan/session](https://github.com/odan/session) library because it is so lightweight and has both session and flash. I think its definitely value added to the project and better than something I would do.   
If in the future I need something more I'll consider [Symfony Sessions](https://github.com/symfony/http-foundation) ([doc](https://odan.github.io/2020/08/09/slim4-http-session.html)) 
* **Database access**: [CakePHP Query Builder](https://book.cakephp.org/4/en/orm/query-builder.html)


## Structure 
```
-- app // contains configuration files
-- public
   -- assets // images, videos, audio files
-- resources 
   -- schema // database table creation schema 
-- src
   -- Application // top layer, contains action classes
   -- Domain // includes business logic / service classes
   -- Infrastrucutre // database access / manipulation 
-- templates 
   -- user // js, css, html file about users
   -- post // js, css, html file about posts
-- tests
   -- Application // application testing
   -- Domain // service testing
   -- Infrastructure // repository testing
```


## Licence
The MIT Licence (MIT). Please see [Licence File](https://github.com/samuelgfeller/slim-example-project/blob/master/LICENCE.txt) for more information.



