# How's the project set-up?

<h2>Directory structure</h2>

The directory structure is based on [Standard PHP Package Skeleton](https://github.com/php-pds/skeleton).

```
├── config                   # contains configuration files
├── public
│   ├── assets               # images, videos, stylesheets, scripts, fonts, audio files
├── resources
│   ├── migrations           # database migrations
│   ├── schema               # database table creation schema
│   ├── seeds                # database seed data
│   └── translations         # translation files
├── src
│   ├── Application          # top layer, contains action classes, middlewares, error handler, responder
│   ├── Domain               # includes business logic / service classes
│   │   ├── [Module]         # domain entities
│   │   │   ├── Service      # domain service classes
│   │   │   └── Repository   # infrastructure repository classes / database access (vertical slice architecture)
│   └── Common               # helper classes and functions
├── templates                # layout and template files for each module 
└── tests
    ├── Integration          # integration tests action class testing which test all layers
    ├── Unit                 # unit tests domain service class testing
    ├── Fixture              # database content to be added as preparation in test db for integration tests
    ├── Provider             # data provider to run the same test cases with different data
    └── Traits               # utility traits (test setup, database connection, helpers)
```

##
