# Slim Example Project

[![Latest Version on Packagist](https://img.shields.io/github/release/samuelgfeller/slim-example-project.svg)](https://packagist.org/packages/samuelgfeller/slim-example-project)
[![Code Coverage](https://scrutinizer-ci.com/g/samuelgfeller/slim-example-project/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/samuelgfeller/slim-example-project/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/samuelgfeller/slim-example-project/badges/build.png?b=master)](https://scrutinizer-ci.com/g/samuelgfeller/slim-example-project/build-status/master)
[![Quality Score](https://img.shields.io/scrutinizer/quality/g/samuelgfeller/slim-example-project.svg)](https://scrutinizer-ci.com/g/samuelgfeller/slim-example-project/?branch=master)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

Real-world example of a modern [Slim 4](https://www.slimframework.com/) web application with a scalable
structure and a variety of components and features.

The project applies current best practices and programming principles,
with a strong emphasis on the Single Responsibility Principle
([SRP](https://github.com/samuelgfeller/slim-example-project/wiki/Single-Responsibility-Principle-(SRP))).   
External library dependencies
are [kept to a minimum](https://github.com/samuelgfeller/slim-example-project/wiki/Libraries-and-Framework)
to facilitate maintenance and ensure long-term viability.

The [architecture](https://github.com/samuelgfeller/slim-example-project/wiki/Architecture)
is inspired by the Domain Driven Design ([DDD](https://en.wikipedia.org/wiki/Domain-driven_design))
and the [Vertical Slice Architecture](https://www.youtube.com/watch?v=L2Wnq0ChAIA).

The base for this project was the official
[Slim-Skeleton](https://github.com/slimphp/Slim-Skeleton) and
the [slim4-skeleton](https://github.com/odan/slim4-skeleton).

A detailed [**documentation**](https://github.com/samuelgfeller/slim-example-project/wiki) explains how the project is
built,
components, design choices and features.

Stripped down versions of this repository are available as skeleton
templates.
With frontend [**slim-starter**](https://github.com/samuelgfeller/slim-starter) or just for an API:
[**slim-api-starter**](https://github.com/samuelgfeller/slim-api-starter).

Please read the [**installation guide**](https://github.com/samuelgfeller/slim-example-project/wiki/Installation-Guide)
to get started.

## Features

All the features were developed with an effort to ensure maximum user-friendliness.
The frontend, intuitive, aesthetically pleasing, minimalistic, and functional.
The backend, efficient and secure.

This project is currently designed for non-profit organizations or foundations that require a platform
to manage and maintain a record of communication through notes of people they help.

**Technologies:**

* [Slim 4 micro-framework](https://github.com/slimphp/Slim)
* [Dependency Injection](https://github.com/samuelgfeller/slim-example-project/wiki/Dependency-Injection) - [PHP-DI](https://php-di.org/)
* [Logging](https://github.com/samuelgfeller/slim-example-project/wiki/Logging) - [Monolog](https://github.com/Seldaek/monolog)
* [Validation](https://github.com/samuelgfeller/slim-example-project/wiki/Validation) - [cakephp/validation](https://book.cakephp.org/4/en/core-libraries/validation.html)
* [Database migrations](https://github.com/samuelgfeller/slim-example-project/wiki/Database-Migrations) - [Phinx](https://phinx.org/)
* [Template rendering](https://github.com/samuelgfeller/slim-example-project/wiki/Template-rendering) - [PHP-View](https://github.com/slimphp/PHP-View)
* [Mailing](https://github.com/samuelgfeller/slim-example-project/wiki/Mailing) - [Symfony Mailer](https://symfony.com/doc/current/mailer.html)
* [Localization](https://github.com/samuelgfeller/slim-example-project/wiki/Translations) - [gettext](https://www.gnu.org/software/gettext/)
* [Query Builder](https://github.com/samuelgfeller/slim-example-project/wiki/Repository-and-Query-Builder) - [cakephp/database](https://book.cakephp.org/5/en/orm/query-builder.html)
* [Integration / unit testing](https://github.com/samuelgfeller/slim-example-project/wiki/Writing-Tests) - [PHPUnit](https://github.com/sebastianbergmann/phpunit/) - [test-traits](https://github.com/samuelgfeller/test-traits)
* [Session and flash messages](https://github.com/samuelgfeller/slim-example-project/wiki/Session-and-Flash-messages) - [odan/session](https://github.com/odan/session)
* [Error handling](https://github.com/samuelgfeller/slim-example-project/wiki/Error-Handling) - [slim-error-renderer](https://github.com/samuelgfeller/slim-error-renderer)
* [GitHub Actions](https://github.com/samuelgfeller/slim-example-project/wiki/GitHub-Actions)
  and [Scrutinizer](https://github.com/samuelgfeller/slim-example-project/wiki/How-to-set-up-Scrutinizer)
* [Coding standards fixer](https://github.com/samuelgfeller/slim-example-project/wiki/Coding-Standards-Fixer) - [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)
* [Static code analysis](https://github.com/samuelgfeller/slim-example-project/wiki/PHPStan-Static-Code-Analysis) - [PHPStan](https://github.com/phpstan/phpstan)

**Functionalities demonstrating real-world features:**

* [Authentication](https://github.com/samuelgfeller/slim-example-project/wiki/Authentication) (login)
* [Authorization](https://github.com/samuelgfeller/slim-example-project/wiki/Authorization) (permissions)
* Account verification
  and [password reset](https://github.com/samuelgfeller/slim-example-project/wiki/Authentication#password-forgotten)
  via email link and token
* [Request throttling](https://github.com/samuelgfeller/slim-example-project/wiki/Security#request-throttling) -
  protection against brute force and password spraying attacks
* User management for administrators
* 4 user roles and different permissions
* User activity history
* Client creation and mutation with status and attributed user
* Client list filtering by text input and filter chips
* Note creation and mutation
* Hidden notes from unauthorized users
* Dashboard with panels
* [Dark / light theme](https://github.com/samuelgfeller/slim-example-project/wiki/Dark-Theme)

<details markdown="1">

<summary><b>Click to see demo</b></summary>

Link: [Login](https://demo.slim-example-project.samuel-gfeller.ch)  
Username: `admin@user.com`  
Password: `12345678`  
The database is regularly reset.

</details>

## Motivation to create this project

There is a ton of great content on the internet about learning how to write clean and sustainable code.
However, I found myself searching for more than just skeleton projects or general documentations
and tutorials when I wanted to learn how to do things within the scope of a potential real-world application.
I never found resources such as an open-source, efficient implementation of all the features surrounding a
full-sized project.

This is what I try to provide here.
This project isn't just a skeleton, it contains a lot of opinionated
practical examples on how to
implement features that are often needed in real-world applications.

That includes authorization, integration testing, localization, validation,
error handling, database migrations, mailing, console commands, request throttling,
lightweight PHP template rendering, GitHub Actions, and more along with detailed
explanations in the documentation.

But it's not just about the features.
Equally important
is the [architecture](https://github.com/samuelgfeller/slim-example-project/wiki/Architecture)
and how the components interact with each other, following modern principles such as the
[Single Responsibility Principle](https://github.com/samuelgfeller/slim-example-project/wiki/Single-Responsibility-Principle-(SRP))
and
[Dependency Injection](https://github.com/samuelgfeller/slim-example-project/wiki/Dependency-Injection).

Of course, there are big frameworks that have their own well-established set of programming conventions and
implementations of features.

However, I find them often
too complex, where the code makes too much "behind the scenes" and with lots of dependencies,
which can lead to time-consuming refactoring on version changes.   
I also dislike having to follow the propitiatory rules of a framework [which often don't
follow best practices](https://www.reddit.com/r/PHP/comments/131t2k1/laravel_considered_harmful)
and
much prefer the freedom of a micro-framework and carefully
[choosing the libraries](https://github.com/samuelgfeller/slim-example-project/wiki/Libraries-and-Framework#choosing-the-right-libraries)
and structure
that make sense for the project.  
This lets me stay in control of the codebase, keep it lightweight,
performant and tailored to the needs of the project, and it's easier to maintain
and adapt to new requirements.

## Disclaimer

This project and its documentation are the result of my personal learning process in the last 6 years
in trying to create the best possible template app with lots of real world examples.
Three of the 6 years were spent full time on this project alone.  
I made what wish I had when I started getting seriously into web development.

The codebase is big and thus lots of subjective decisions had to be made that may not be the best
long-term solution for everybody.

The main focus throughout the development was to make the code as long living as possible
with best practices and few dependencies so that it can be adapted to different needs and
preferences.

Basically, this is my take on what an efficient, extensible, and maintainable web app could look like with today's
tech.

I worked closely with the software engineer and architect
[Daniel Opitz](https://odan.github.io/about.html), who also reviewed this project.
I learned a lot during
[our exchanges](https://github.com/samuelgfeller/slim-example-project/wiki/Sources-of-knowledge#discussions)
and was inspired by his books, articles, tutorials and his slim
[skeleton-project](https://github.com/odan/slim4-skeleton).

## Support

Please read the [Support❤️](https://github.com/samuelgfeller/slim-example-project/wiki/Support❤️) page
if you value this project and its documentation and want to support it.

## License

This project is licensed under the MIT License — see the
[LICENSE](https://github.com/samuelgfeller/slim-example-project/blob/master/LICENSE) file for details.
