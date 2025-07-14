# Slim Example Project

[![Documentation](https://img.shields.io/badge/Documentation-787CB5?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjwhLS0gVXBsb2FkZWQgdG86IFNWRyBSZXBvLCB3d3cuc3ZncmVwby5jb20sIFRyYW5zZm9ybWVkIGJ5OiBTVkcgUmVwbyBNaXhlciBUb29scyAtLT4KPHN2ZyB3aWR0aD0iODAwcHgiIGhlaWdodD0iODAwcHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KDTxnIGlkPSJTVkdSZXBvX2JnQ2FycmllciIgc3Ryb2tlLXdpZHRoPSIwIi8%2BCg08ZyBpZD0iU1ZHUmVwb190cmFjZXJDYXJyaWVyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KDTxnIGlkPSJTVkdSZXBvX2ljb25DYXJyaWVyIj4gPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik05Ljk0NTMxIDEuMjVIMTQuMDU1MUMxNS40MjI3IDEuMjQ5OTggMTYuNTI1IDEuMjQ5OTYgMTcuMzkxOSAxLjM2NjUyQzE4LjI5MiAxLjQ4NzU0IDE5LjA0OTkgMS43NDY0MyAxOS42NTE4IDIuMzQ4MzVDMjAuMjUzOCAyLjk1MDI3IDIwLjUxMjYgMy43MDgxNCAyMC42MzM3IDQuNjA4MjVDMjAuNzUwMiA1LjQ3NTIyIDIwLjc1MDIgNi41Nzc1NCAyMC43NTAyIDcuOTQ1MTNWMTYuMDU0OUMyMC43NTAyIDE3LjQyMjUgMjAuNzUwMiAxOC41MjQ4IDIwLjYzMzcgMTkuMzkxOEMyMC41MTI2IDIwLjI5MTkgMjAuMjUzOCAyMS4wNDk3IDE5LjY1MTggMjEuNjUxN0MxOS4wNDk5IDIyLjI1MzYgMTguMjkyIDIyLjUxMjUgMTcuMzkxOSAyMi42MzM1QzE2LjUyNSAyMi43NSAxNS40MjI2IDIyLjc1IDE0LjA1NTEgMjIuNzVIOS45NDUzMkM4LjU3NzczIDIyLjc1IDcuNDc1NCAyMi43NSA2LjYwODQ0IDIyLjYzMzVDNS43MDgzMyAyMi41MTI1IDQuOTUwNDUgMjIuMjUzNiA0LjM0ODU0IDIxLjY1MTdDMy43NDY2MiAyMS4wNDk3IDMuNDg3NzMgMjAuMjkxOSAzLjM2NjcxIDE5LjM5MThDMy4zMjgwMSAxOS4xMDM5IDMuMzAyMTYgMTguNzkwMiAzLjI4NDkgMTguNDQ5NEMzLjI0NTgyIDE4LjMyNiAzLjIzODIxIDE4LjE5MTIgMy4yNjg5NSAxOC4wNTY4QzMuMjUwMTYgMTcuNDY0OSAzLjI1MDE3IDE2Ljc5OTEgMy4yNTAxOSAxNi4wNTQ5VjcuOTQ1MTNDMy4yNTAxNyA2LjU3NzU0IDMuMjUwMTUgNS40NzUyMiAzLjM2NjcxIDQuNjA4MjVDMy40ODc3MyAzLjcwODE0IDMuNzQ2NjIgMi45NTAyNyA0LjM0ODU0IDIuMzQ4MzVDNC45NTA0NSAxLjc0NjQzIDUuNzA4MzMgMS40ODc1NCA2LjYwODQzIDEuMzY2NTJDNy40NzU0IDEuMjQ5OTYgOC41Nzc3MiAxLjI0OTk4IDkuOTQ1MzEgMS4yNVpNNC43NzY5NCAxOC4yNDkxQzQuNzkyMTQgMTguNjAyOSA0LjgxNTk3IDE4LjkxNCA0Ljg1MzMzIDE5LjE5MTlDNC45NTE5OSAxOS45MjU3IDUuMTMyNDMgMjAuMzE0MiA1LjQwOTIgMjAuNTkxQzUuNjg1OTYgMjAuODY3OCA2LjA3NDUzIDIxLjA0ODIgNi44MDgzMSAyMS4xNDY5QzcuNTYzNjYgMjEuMjQ4NCA4LjU2NDc3IDIxLjI1IDEwLjAwMDIgMjEuMjVIMTQuMDAwMkMxNS40MzU2IDIxLjI1IDE2LjQzNjcgMjEuMjQ4NCAxNy4xOTIxIDIxLjE0NjlDMTcuOTI1OCAyMS4wNDgyIDE4LjMxNDQgMjAuODY3OCAxOC41OTEyIDIwLjU5MUMxOC43ODc1IDIwLjM5NDcgMTguOTM1MyAyMC4xNDIxIDE5LjAzOTkgMTkuNzVIOC4wMDAxOUM3LjU4NTk3IDE5Ljc1IDcuMjUwMTkgMTkuNDE0MiA3LjI1MDE5IDE5QzcuMjUwMTkgMTguNTg1OCA3LjU4NTk3IDE4LjI1IDguMDAwMTkgMTguMjVIMTkuMjIzNEMxOS4yNDE5IDE3LjgxOSAxOS4yNDc3IDE3LjMyNDYgMTkuMjQ5NCAxNi43NUg3Ljg5Nzk2QzYuOTE5NzEgMTYuNzUgNi41Nzc3IDE2Ljc1NjQgNi4zMTU2MiAxNi44MjY3QzUuNTk2MyAxNy4wMTk0IDUuMDIyODYgMTcuNTU0MSA0Ljc3Njk0IDE4LjI0OTFaTTE5LjI1MDIgMTUuMjVINy44OTc5NkM3Ljg1ODc5IDE1LjI1IDcuODIwMiAxNS4yNSA3Ljc4MjE3IDE1LjI1QzYuOTY0MiAxNS4yNDk3IDYuNDA2MDUgMTUuMjQ5NSA1LjkyNzM5IDE1LjM3NzhDNS40OTk0MSAxNS40OTI1IDUuMTAyNDIgMTUuNjc5OCA0Ljc1MDE5IDE1LjkyNTlWOEM0Ljc1MDE5IDYuNTY0NTggNC43NTE3OCA1LjU2MzQ3IDQuODUzMzMgNC44MDgxMkM0Ljk1MTk5IDQuMDc0MzUgNS4xMzI0MyAzLjY4NTc3IDUuNDA5MiAzLjQwOTAxQzUuNjg1OTYgMy4xMzIyNSA2LjA3NDUzIDIuOTUxOCA2LjgwODMxIDIuODUzMTVDNy41NjM2NiAyLjc1MTU5IDguNTY0NzcgMi43NSAxMC4wMDAyIDIuNzVIMTQuMDAwMkMxNS40MzU2IDIuNzUgMTYuNDM2NyAyLjc1MTU5IDE3LjE5MjEgMi44NTMxNUMxNy45MjU4IDIuOTUxOCAxOC4zMTQ0IDMuMTMyMjUgMTguNTkxMiAzLjQwOTAxQzE4Ljg2NzkgMy42ODU3NyAxOS4wNDg0IDQuMDc0MzUgMTkuMTQ3IDQuODA4MTJDMTkuMjQ4NiA1LjU2MzQ3IDE5LjI1MDIgNi41NjQ1OCAxOS4yNTAyIDhWMTUuMjVaTTcuMjUwMTkgN0M3LjI1MDE5IDYuNTg1NzkgNy41ODU5NyA2LjI1IDguMDAwMTkgNi4yNUgxNi4wMDAyQzE2LjQxNDQgNi4yNSAxNi43NTAyIDYuNTg1NzkgMTYuNzUwMiA3QzE2Ljc1MDIgNy40MTQyMSAxNi40MTQ0IDcuNzUgMTYuMDAwMiA3Ljc1SDguMDAwMTlDNy41ODU5NyA3Ljc1IDcuMjUwMTkgNy40MTQyMSA3LjI1MDE5IDdaTTcuMjUwMTkgMTAuNUM3LjI1MDE5IDEwLjA4NTggNy41ODU5NyA5Ljc1IDguMDAwMTkgOS43NUgxMy4wMDAyQzEzLjQxNDQgOS43NSAxMy43NTAyIDEwLjA4NTggMTMuNzUwMiAxMC41QzEzLjc1MDIgMTAuOTE0MiAxMy40MTQ0IDExLjI1IDEzLjAwMDIgMTEuMjVIOC4wMDAxOUM3LjU4NTk3IDExLjI1IDcuMjUwMTkgMTAuOTE0MiA3LjI1MDE5IDEwLjVaIiBmaWxsPSIjZmZmIi8%2BIDwvZz4KDTwvc3ZnPg%3D%3D&labelColor=grey)](https://samuel-gfeller.ch/docs)
[![Installation](https://img.shields.io/badge/Installation-blue?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjwhLS0gVXBsb2FkZWQgdG86IFNWRyBSZXBvLCB3d3cuc3ZncmVwby5jb20sIFRyYW5zZm9ybWVkIGJ5OiBTVkcgUmVwbyBNaXhlciBUb29scyAtLT4KPHN2ZyB3aWR0aD0iODAwcHgiIGhlaWdodD0iODAwcHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KDTxnIGlkPSJTVkdSZXBvX2JnQ2FycmllciIgc3Ryb2tlLXdpZHRoPSIwIi8%2BCg08ZyBpZD0iU1ZHUmVwb190cmFjZXJDYXJyaWVyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KDTxnIGlkPSJTVkdSZXBvX2ljb25DYXJyaWVyIj4gPHBhdGggZD0iTTEyLjU1MzUgMTYuNTA2MUMxMi40MTE0IDE2LjY2MTUgMTIuMjEwNiAxNi43NSAxMiAxNi43NUMxMS43ODk0IDE2Ljc1IDExLjU4ODYgMTYuNjYxNSAxMS40NDY1IDE2LjUwNjFMNy40NDY0OCAxMi4xMzExQzcuMTY2OTggMTEuODI1NCA3LjE4ODIyIDExLjM1MSA3LjQ5MzkyIDExLjA3MTVDNy43OTk2MyAxMC43OTIgOC4yNzQwMiAxMC44MTMyIDguNTUzNTIgMTEuMTE4OUwxMS4yNSAxNC4wNjgyVjNDMTEuMjUgMi41ODU3OSAxMS41ODU4IDIuMjUgMTIgMi4yNUMxMi40MTQyIDIuMjUgMTIuNzUgMi41ODU3OSAxMi43NSAzVjE0LjA2ODJMMTUuNDQ2NSAxMS4xMTg5QzE1LjcyNiAxMC44MTMyIDE2LjIwMDQgMTAuNzkyIDE2LjUwNjEgMTEuMDcxNUMxNi44MTE4IDExLjM1MSAxNi44MzMgMTEuODI1NCAxNi41NTM1IDEyLjEzMTFMMTIuNTUzNSAxNi41MDYxWiIgZmlsbD0iI2ZmZiIvPiA8cGF0aCBkPSJNMy43NSAxNUMzLjc1IDE0LjU4NTggMy40MTQyMiAxNC4yNSAzIDE0LjI1QzIuNTg1NzkgMTQuMjUgMi4yNSAxNC41ODU4IDIuMjUgMTVWMTUuMDU0OUMyLjI0OTk4IDE2LjQyMjUgMi4yNDk5NiAxNy41MjQ4IDIuMzY2NTIgMTguMzkxOEMyLjQ4NzU0IDE5LjI5MTkgMi43NDY0MyAyMC4wNDk3IDMuMzQ4MzUgMjAuNjUxNkMzLjk1MDI3IDIxLjI1MzYgNC43MDgxNCAyMS41MTI1IDUuNjA4MjUgMjEuNjMzNUM2LjQ3NTIyIDIxLjc1IDcuNTc3NTQgMjEuNzUgOC45NDUxMyAyMS43NUgxNS4wNTQ5QzE2LjQyMjUgMjEuNzUgMTcuNTI0OCAyMS43NSAxOC4zOTE4IDIxLjYzMzVDMTkuMjkxOSAyMS41MTI1IDIwLjA0OTcgMjEuMjUzNiAyMC42NTE3IDIwLjY1MTZDMjEuMjUzNiAyMC4wNDk3IDIxLjUxMjUgMTkuMjkxOSAyMS42MzM1IDE4LjM5MThDMjEuNzUgMTcuNTI0OCAyMS43NSAxNi40MjI1IDIxLjc1IDE1LjA1NDlWMTVDMjEuNzUgMTQuNTg1OCAyMS40MTQyIDE0LjI1IDIxIDE0LjI1QzIwLjU4NTggMTQuMjUgMjAuMjUgMTQuNTg1OCAyMC4yNSAxNUMyMC4yNSAxNi40MzU0IDIwLjI0ODQgMTcuNDM2NSAyMC4xNDY5IDE4LjE5MTlDMjAuMDQ4MiAxOC45MjU3IDE5Ljg2NzggMTkuMzE0MiAxOS41OTEgMTkuNTkxQzE5LjMxNDIgMTkuODY3OCAxOC45MjU3IDIwLjA0ODIgMTguMTkxOSAyMC4xNDY5QzE3LjQzNjUgMjAuMjQ4NCAxNi40MzU0IDIwLjI1IDE1IDIwLjI1SDlDNy41NjQ1OSAyMC4yNSA2LjU2MzQ3IDIwLjI0ODQgNS44MDgxMiAyMC4xNDY5QzUuMDc0MzUgMjAuMDQ4MiA0LjY4NTc3IDE5Ljg2NzggNC40MDkwMSAxOS41OTFDNC4xMzIyNSAxOS4zMTQyIDMuOTUxOCAxOC45MjU3IDMuODUzMTUgMTguMTkxOUMzLjc1MTU5IDE3LjQzNjUgMy43NSAxNi40MzU0IDMuNzUgMTVaIiBmaWxsPSIjZmZmIi8%2BIDwvZz4KDTwvc3ZnPg%3D%3D&logoColor=blue&labelColor=grey)](https://samuel-gfeller.ch/docs/Slim-Example-Project-Installation-Guide)
[![Code Coverage](https://scrutinizer-ci.com/g/samuelgfeller/slim-example-project/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/samuelgfeller/slim-example-project/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/samuelgfeller/slim-example-project/badges/build.png?b=master)](https://scrutinizer-ci.com/g/samuelgfeller/slim-example-project/build-status/master)
[![Quality Score](https://img.shields.io/scrutinizer/quality/g/samuelgfeller/slim-example-project.svg)](https://scrutinizer-ci.com/g/samuelgfeller/slim-example-project/?branch=master)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

Real-world example of a modern [Slim 4](https://www.slimframework.com/) web application with a scalable
structure and a variety of components and features.

The project applies current best practices and programming principles,
with a strong emphasis on the [SOLID](https://en.wikipedia.org/wiki/SOLID) 
Single Responsibility Principle ([SRP](https://samuel-gfeller.ch/docs/Single-Responsibility-Principle-(SRP))).   
External library dependencies
are [kept to a minimum](https://samuel-gfeller.ch/docs/Libraries-and-Framework)
to facilitate maintenance and ensure long-term viability.

The [architecture](https://samuel-gfeller.ch/docs/Architecture)
is inspired by the Domain Driven Design ([DDD](https://en.wikipedia.org/wiki/Domain-driven_design))
and the [Vertical Slice Architecture](https://www.youtube.com/watch?v=L2Wnq0ChAIA).

A detailed [documentation](https://samuel-gfeller.ch/docs) explains how the project is
built,
components, design choices and features.

Stripped down versions of this repository are available as skeleton
templates.
With frontend [**slim-starter**](https://github.com/samuelgfeller/slim-starter) or just for an API:
[**slim-api-starter**](https://github.com/samuelgfeller/slim-api-starter).

Please read the [**installation guide**](https://samuel-gfeller.ch/docs/Slim-Example-Project-Installation-Guide)
to get started.

## Features

All the features were developed with an effort to ensure maximum user-friendliness.
The frontend, intuitive, aesthetically pleasing, minimalistic, and functional.
The backend, efficient and secure.

**Technologies:**

* [Slim 4 micro-framework](https://github.com/slimphp/Slim)
* [Dependency Injection](https://samuel-gfeller.ch/docs/Dependency-Injection) - [PHP-DI](https://php-di.org/)
* [Logging](https://samuel-gfeller.ch/docs/Logging) - [Monolog](https://github.com/Seldaek/monolog)
* [Validation](https://samuel-gfeller.ch/docs/Validation) - [cakephp/validation](https://book.cakephp.org/4/en/core-libraries/validation.html)
* [Database migrations](https://samuel-gfeller.ch/docs/Database-Migrations) - [Phinx](https://phinx.org/)
* [Template rendering](https://samuel-gfeller.ch/docs/Template-Rendering) - [PHP-View](https://github.com/slimphp/PHP-View)
* [Mailing](https://samuel-gfeller.ch/docs/Mailing) - [Symfony Mailer](https://symfony.com/doc/current/mailer.html)
* [Localization](https://samuel-gfeller.ch/docs/Translations) - [gettext](https://www.gnu.org/software/gettext/)
* [Query Builder](https://samuel-gfeller.ch/docs/Repository-and-Query-Builder) - [cakephp/database](https://book.cakephp.org/5/en/orm/query-builder.html)
* [Integration / unit testing](https://samuel-gfeller.ch/docs/Writing-Tests) - [PHPUnit](https://github.com/sebastianbergmann/phpunit/) - [test-traits](https://github.com/samuelgfeller/test-traits)
* [Session and flash messages](https://samuel-gfeller.ch/docs/Session-and-Flash-messages) - [odan/session](https://github.com/odan/session)
* [Error handling](https://samuel-gfeller.ch/docs/Error-Handling) - [slim-error-renderer](https://github.com/samuelgfeller/slim-error-renderer)
* [GitHub Actions](https://samuel-gfeller.ch/docs/GitHub-Actions)
  and [Scrutinizer](https://samuel-gfeller.ch/docs/How-to-set-up-Scrutinizer)
* [Coding standards fixer](https://samuel-gfeller.ch/docs/Coding-Standards-Fixer) - [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)
* [Static code analysis](https://samuel-gfeller.ch/docs/PHPStan-Static-Code-Analysis) - [PHPStan](https://github.com/phpstan/phpstan)

**Functionalities demonstrating real-world features:**  

This project is currently designed for non-profit organizations or foundations that require a platform
to manage and maintain a record of communication through notes of people they help.

* [Authentication](https://samuel-gfeller.ch/docs/Authentication) (login)
* [Authorization](https://samuel-gfeller.ch/docs/Authorization) (permissions)
* Account verification
  and [password reset](https://samuel-gfeller.ch/docs/Authentication#password-forgotten)
  via email link and token
* [Request throttling](https://samuel-gfeller.ch/docs/Security#request-throttling) -
  protection against brute force and password spraying attacks
* User management for administrators
* 4 user roles and different permissions
* User activity history
* Client creation and mutation with status and attributed user
* Client list filtering by text input and filter chips
* Note creation and mutation
* Hidden notes from unauthorized users
* Dashboard with panels
* [Dark / light theme](https://samuel-gfeller.ch/docs/Dark-Theme)

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
implement features that are often necessary in real-world applications.

That includes authorization, integration testing, localization, validation,
error handling, database migrations, mailing, console commands, request throttling,
lightweight PHP template rendering, GitHub Actions, and more along with detailed
explanations in the documentation.

But it's not just about the features.
Equally important
are the [architecture](https://samuel-gfeller.ch/docs/Architecture)
and how the components interact with each other, following modern principles such as the
[Single Responsibility Principle](https://samuel-gfeller.ch/docs/Single-Responsibility-Principle-(SRP))
and
[Dependency Injection](https://samuel-gfeller.ch/docs/Dependency-Injection).

Of course, there are big frameworks that have their own well-established set of programming conventions and
implementations of features.

However, I find them often
too complex, where the code makes too much "behind the scenes" and with lots of dependencies,
which can lead to time-consuming refactoring on version changes.   
I also dislike having to follow the propitiatory rules of a framework [which often don't
follow best practices](https://www.reddit.com/r/PHP/comments/131t2k1/laravel_considered_harmful)
and
much prefer the freedom of a micro-framework and carefully
[choosing the libraries](https://samuel-gfeller.ch/docs/Libraries-and-Framework)
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
[our exchanges](https://samuel-gfeller.ch/docs/Sources-of-Knowledge#discussions)
and was inspired by his books, articles, tutorials and his slim
[skeleton-project](https://github.com/odan/slim4-skeleton).

## Support

Please read the [Support❤️](https://samuel-gfeller.ch/docs/Support❤️) page
if you value this project and its documentation and want to support it.

## License

This project is licensed under the MIT License — see the
[LICENSE](https://github.com/samuelgfeller/slim-example-project/blob/master/LICENSE) file for details.
