# Slim Example Project

[![Latest Version on Packagist](https://img.shields.io/github/release/samuelgfeller/slim-example-project.svg)](https://packagist.org/packages/samuelgfeller/slim-example-project)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=coverage)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)
[![Lines of Code](https://sonarcloud.io/api/project_badges/measure?project=samuelgfeller_slim-example-project&metric=ncloc)](https://sonarcloud.io/summary/new_code?id=samuelgfeller_slim-example-project)

This project aims to be a real-world example of a modern [Slim 4](https://www.slimframework.com/) 
web application with a scalable structure and
a range of practical components and features.

It showcases the implementation of a simple yet robust 
[architecture](https://github.com/samuelgfeller/slim-example-project/wiki/Architecture) 
with a variety of backend and
frontend features built using the Slim 4 micro-framework. 
The base for this project was the official 
[Slim-Skeleton](https://github.com/slimphp/Slim-Skeleton) and Odan's [slim4-skeleton](https://github.com/odan/slim4-skeleton).

This repository can serve as a learning example or be adapted for developing new
applications. 

External library dependencies are [kept to a minimum](https://github.com/samuelgfeller/slim-example-project/wiki/Libraries-and-Framework) 
to facilitate maintenance and ensure long-term viability. 

Current best practices and modern principles are applied throughout the project. 
Extra care was taken to follow the 
Single Responsibility Principle ([SRP](https://github.com/samuelgfeller/slim-example-project/wiki/Single-Responsibility-Principle-(SRP))).

A detailed [**documentation**](https://github.com/samuelgfeller/slim-example-project/wiki) explains the project structure, components, design choices and features.

Please read the [**installation guide**](https://github.com/samuelgfeller/slim-example-project/wiki/Installation-Guide)
to get started.

Stripped down versions of this repository are available as skeleton 
templates.
With frontend [`slim-starter`](https://github.com/samuelgfeller/slim-starter) or just for an API:
[`slim-api-starter`](https://github.com/samuelgfeller/slim-api-starter).

## Features
All the features were developed with an effort to ensure maximum user-friendliness. 
The frontend, intuitive, aesthetically pleasing, minimalistic, and functional.
The backend, efficient and secure.

This project is currently designed for non-profit organizations or foundations that require a platform
to manage and maintain a record of communication through notes of people they help.

**Project components:**

* [Dependency Injection](https://github.com/samuelgfeller/slim-example-project/wiki/Dependency-Injection)
* [Session and flash messages](https://github.com/samuelgfeller/slim-example-project/wiki/Session-and-Flash-messages)
* [Authentication](https://github.com/samuelgfeller/slim-example-project/wiki/Authentication) (login) 
  and [authorization](https://github.com/samuelgfeller/slim-example-project/wiki/Authorization) (permissions)
* Account verification and [password reset](https://github.com/samuelgfeller/slim-example-project/wiki/Authentication#password-forgotten) 
  via email link and token
* [Request throttling](https://github.com/samuelgfeller/slim-example-project/wiki/Security#request-throttling) - 
  protection against rapid fire and distributed brute force attacks (time throttling and captcha)
* [Localization](https://github.com/samuelgfeller/slim-example-project/wiki/Translations) - English, German and French
* [Validation](https://github.com/samuelgfeller/slim-example-project/wiki/Validation)
* [Template rendering](https://github.com/samuelgfeller/slim-example-project/wiki/Template-rendering) with native PHP syntax (easily interchangeable with twig)
* [Dark theme](https://github.com/samuelgfeller/slim-example-project/wiki/Dark-Theme)
* [Advanced error handling](https://github.com/samuelgfeller/slim-example-project/wiki/Error-Handling)
* [Integration & unit testing](https://github.com/samuelgfeller/slim-example-project/wiki/Writing-Tests)
  with fixtures and data providers
* [Database migrations](https://github.com/samuelgfeller/slim-example-project/wiki/Database-Migrations) and [seeding](https://github.com/samuelgfeller/slim-example-project/wiki/Database-Migrations#seeding)
* [Query Builder](https://github.com/samuelgfeller/slim-example-project/wiki/Repository-and-Query-Builder)
* [Logging](https://github.com/samuelgfeller/slim-example-project/wiki/Logging)
* [Mailing](https://github.com/samuelgfeller/slim-example-project/wiki/Mailing)
* [Simple console commands](https://github.com/samuelgfeller/slim-example-project/wiki/Console-Commands)
* [Scrutinizer](https://github.com/samuelgfeller/slim-example-project/wiki/How-to-set-up-Scrutinizer)
* [GitHub Actions](https://github.com/samuelgfeller/slim-example-project/wiki/GitHub-Actions)

**Functionalities demonstrating real-world features:**

* User management for administrators
* 4 user roles and different permissions
* User activity history
* Client creation and mutation with status and attributed user
* Client list filtering by text input and filter chips
* Note creation and mutation
* Hidden notes from unauthorized users
* Dashboard with panels


<details>

<summary><b>Click to see demo</b></summary>

Link: [Login](https://demo.slim-example-project.samuel-gfeller.ch)  
Username: `admin@user.com`  
Password: `12345678`  
The database is reset regularly.

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
[Single Responsibility Principle](https://github.com/samuelgfeller/slim-example-project/wiki/Single-Responsibility-Principle-(SRP)) and 
[Dependency Injection](https://github.com/samuelgfeller/slim-example-project/wiki/Dependency-Injection).

Of course, there are big frameworks that have their own well-established set of programming conventions and
implementations of features.
 
However, I find them often
too complex, where the code makes too much "behind the scenes" and with lots of dependencies,
which can lead to time-consuming refactoring on version changes.   
I also dislike having to follow the propitiatory rules of a framework and
much prefer the freedom of a micro-framework and carefully
[choosing the libraries](https://github.com/samuelgfeller/slim-example-project/wiki/Libraries-and-Framework#choosing-the-right-libraries) 
I want to use.  
This lets me stay in control of the codebase, keep it lightweight, 
performant and tailored to the needs of the project, and it's easier to maintain 
and adapt to new requirements.  

You can very well adapt it to your own needs as well, remove or add features, and change the libraries.

## Disclaimer
This project and its documentation are the result of my personal learning process in the last 6 years
in trying to create the best possible template app with lots of real world examples.
Three of the 6 years were spent full time on this project alone.  
I made what wish I had when I started getting seriously into web development.  

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

## Support
Please read the [Support❤️](https://github.com/samuelgfeller/slim-example-project/wiki/Support❤️) page 
if you value this project and its documentation and want to support it.

## License
This project is licensed under the MIT License — see the 
[LICENSE](LICENSE) file for details.
