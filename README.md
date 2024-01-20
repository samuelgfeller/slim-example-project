# Slim example project

[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=coverage)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)
[![Lines of Code](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=ncloc)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)

This project showcases a real-world-example of a backend and frontend built using the
[Slim](https://www.slimframework.com/) micro-framework.

The primary goal is to provide a modern codebase with a scalable project structure and 
a range of practical feature implementations.
These can serve as learning examples or be adapted for developing new 
applications. 

External library dependencies are kept to a minimum to facilitate maintenance and 
ensure long-term viability.

## [Installation guide](https://github.com/samuelgfeller/slim-example-project/wiki/Installation-guide)

## Features
All features were developed with an effort to ensure maximum user-friendliness. 
It is important to me that the frontend is intuitive, aesthetically pleasing, minimalistic, and functional.

Project components:
* Authentication (login) and authorization (permissions)
* Account verification and password reset via email link and token
* Protection against rapid fire brute force and password spraying attacks (time throttling and
  captcha) - [docs](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/security-concept.md)
* Localization — English, German and French
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
* Notes hidden from unauthorized users 
* Dashboard with panels

<details>
  <summary><h3>View demo</h3></summary>

The project is currently designed for non-profit organizations or foundations that require a platform 
to manage the people they assist and maintain a record of communication through notes.

Link: [demo.slim-example-project.samuel-gfeller.ch](https://demo.slim-example-project.samuel-gfeller.ch)  
Usernames: `admin@user.com`, `managing-advisor@user.com`, `advisor@user.com`, `newcomer@user.com`  
Password: `12345678`  
The database is reset every half-hour.

</details>

## Documentation

**Basics of this Slim app**
 * [Composer Setup](https://github.com/samuelgfeller/slim-example-project/wiki/Composer)
 * [Webserver config and bootstrapping](https://github.com/samuelgfeller/slim-example-project/wiki/Webserver-config-and-bootstrapping)
 * [Dependency Injection](https://github.com/samuelgfeller/slim-example-project/wiki/Dependency-Injection)
 * [Configuration](https://github.com/samuelgfeller/slim-example-project/wiki/Configuration)
 * [Routing and Middleware](https://github.com/samuelgfeller/slim-example-project/wiki/Routing-and-middleware)
 * [Architecture](https://github.com/samuelgfeller/slim-example-project/wiki/Architecture)
 * [Single Responsibility Principle](https://github.com/samuelgfeller/slim-example-project/wiki/Single-Responsibility-Principle-(SRP))
 * [Action](https://github.com/samuelgfeller/slim-example-project/wiki/Single-Action-Controller)
 * [Domain](https://github.com/samuelgfeller/slim-example-project/wiki/Domain)
 * [Repository](https://github.com/samuelgfeller/slim-example-project/wiki/Repository)

**Features**
 * [Logging](https://github.com/samuelgfeller/slim-example-project/wiki/Logging)
 * [Validation](https://github.com/samuelgfeller/slim-example-project/wiki/Validation)
 * [Session and Flash](https://github.com/samuelgfeller/slim-example-project/wiki/Session-and-Flash-messages)
 * [Authentication](https://github.com/samuelgfeller/slim-example-project/wiki/Authentication)
 * [Authorization](https://github.com/samuelgfeller/slim-example-project/wiki/Authorization)
 * [Translations](https://github.com/samuelgfeller/slim-example-project/wiki/Translations)
 * [Mailing](https://github.com/samuelgfeller/slim-example-project/wiki/Mailing)
 * [Console Commands](https://github.com/samuelgfeller/slim-example-project/wiki/Console-Commands)
 * [Database migrations](https://github.com/samuelgfeller/slim-example-project/wiki/Database-Migrations)
 * [Error Handling](https://github.com/samuelgfeller/slim-example-project/wiki/Error-Handling)
 * [Security](https://github.com/samuelgfeller/slim-example-project/wiki/Security)
 * [GitHub Actions](https://github.com/samuelgfeller/slim-example-project/wiki/GitHub-Actions)

**Testing**
 * [Testing](https://github.com/samuelgfeller/slim-example-project/wiki/Testing)
 * [Writing Tests](https://github.com/samuelgfeller/slim-example-project/wiki/Writing-Tests)
 * [Test Examples](https://github.com/samuelgfeller/slim-example-project/wiki/Test-Examples)

**Frontend**
* [Template rendering](https://github.com/samuelgfeller/slim-example-project/wiki/Template-rendering)
* [Dark mode - (coming soon)]()
* [JS Modules - (coming soon)]()
* [Ajax - (coming soon)](https://github.com/samuelgfeller/slim-example-project/wiki/Ajax)

**Other**
 * [Directory structure](https://github.com/samuelgfeller/slim-example-project/wiki/Directory-structure)
 * [Libraries and Framework](https://github.com/samuelgfeller/slim-example-project/wiki/Libraries-and-Framework)
 * [Project cheatsheet - (coming soon)]()
 * [Dev journal](https://github.com/samuelgfeller/slim-example-project/wiki/Dev-journal)
 * [Sources of knowledge](https://github.com/samuelgfeller/slim-example-project/wiki/Sources-of-knowledge)

## The reason this project was made

There is a ton of great content on the internet about learning how to write clean and sustainable code. 
However, I found myself searching for more than just skeleton projects or general documentations
and tutorials when I wanted to learn how to do things within the scope of a potential real-world application.
I never found resources such as an open-source, efficient implementation of all the features surrounding a
full-sized project.

This is what I try to provide here. 
This project isn't just a skeleton, it contains a lot of practical examples on how to 
implement features that are often needed in real-world applications.

One example of the things I was looking for when I wanted to learn how to build a scalable project
was how to build complex integration test cases such as authorization of actions with different 
roles in different contexts.   
This project contains real examples and documentation with it in the [testing examples](https://github.com/samuelgfeller/slim-example-project/wiki/Testing-Examples)
which also addresses _what_ should be tested.

Another example is the implementation of a robust security concept. How to protect against brute force
attacks? Or XSS attacks or against email spamming?  
This isn't perfect, but there is a relatively simple
[implementation](https://github.com/samuelgfeller/slim-example-project/wiki/Security) of these
concepts in this project.

Authorization, localization, validation, error handling, database migrations and lightweight
PHP template rendering are other examples of features I struggled to find 
open-source lightweight real-world-like implementations.

Of course, there are big frameworks that have answers to all these problems. 
However, I find them often
too complex, where the code makes too much "behind the scenes" combined with a high dependency
and time-consuming refactoring on version changes.  
I also dislike having to follow the propitiatory conventions of a framework and
much prefer the freedom of a micro-framework and choosing the libraries that make most sense.   
This lets me stay in control of the codebase, keep it lightweight, 
performant and tailored to my needs.

## Disclaimer
This project and its documentation are the result of my personal learning process in the last 6 years
in trying to create the best possible template app with lots of real world examples.  
I'm making what wish I had when I started getting seriously into web development.  

The codebase is big and thus lots of subjective decisions had to be made that may not be the best
long-term solution for everybody.   

The main focus throughout the development was to make the code as dependency free as possible 
so that it's long living and can be adapted to different needs and preferences.

Basically, this is my take on what a modern and efficient web app could look like with today's
tech.

I worked closely with the software architect 
[Daniel Opitz](https://odan.github.io/about.html), who also reviewed this project.
I learned a lot during 
[our exchanges](https://github.com/samuelgfeller/slim-example-project/wiki/Sources-of-knowledge#discussions)
and was inspired by his books, articles, tutorials and his slim 
[skeleton-project](https://github.com/odan/slim4-skeleton).  
I'm grateful for his support and the time he took to help me improve this project.

## Credits

Special thanks to [JetBrains](https://jb.gg/OpenSource) for supporting this project.

## Licence

The MIT Licence (MIT). Please
see the [Licence File](https://github.com/samuelgfeller/slim-example-project/blob/master/LICENCE.txt) 
for more information.
