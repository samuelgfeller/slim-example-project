# Configuration

The installation guide is in the project 
[readme.md](slim-example-project/blob/master/readme.md).

There are different kinds of configurations with different visibility.

### Directory
The framework main files are located inside the directory `config/`.   
The project configuration values are inside `config/local/`.

### Default values
The default and non-sensitive config values are inside `config/local/defaults.php`.  
It should contain all keys even when values are null to act as template 
that will be overwritten in the secret `env.php` file.

### Secret values

Environment specific secret values are in `config/local/env.php`.    
This file should be added to the `.gitignore` file to not be pushed accidentally.

### Environment specific non-secret values
#### Development
Development env values are in the file `env.dev.php`. This file contains
every non-secret configuration on the development machine such as
error reporting and database name.
When testing, this file won't be loaded so everything relevant for testing
that is not in or different from `defaults.php` should also be to the
`env.test.php` file.

#### Production
Production env values are in the file `config/local/env.prod.php`. This file contains
every non-secret configuration on the production environment.  
For the production config values to be loaded, the following line has to be in 
the prod secret `env.php`:  
```php
$_ENV['APP_ENV'] = 'prod';
```

#### Testing
The environment values for integration testing (e.g. database name) are stored inside 
`config/local/env.test.php`.

### Config values usage
`config/settings.php` combines and returns all the relevant configuration 
files values.  

They are loaded in this order:  
1. File `config/local/defaults.php`
2. File `config/local/env.php`
3. Depending on  what `APP_ENV` is defined, the environment specific file is loaded
   (if `APP_ENV` is "test", it will load `env.test.php`, if it is "dev" it'll load 
   `env.dev.php` and if it's "prod", it'll load `env.prod.php`).