# Configuration
### Introduction
The configuration techniques used in this project are greatly inspired by the configuration of 
[odan/slim4-skeleton](https://odan.github.io/slim4-skeleton/configuration.html). 

## Configuration
There are different kinds of configurations with different visibility.   

### Directory
The configuration files are all located inside the directory `app/`  

### Default values
The default and public config values are inside `app/defaults.php`.  
This file can be pushed to the remote repository as it contains no secret values.   
It should contain all keys even when values are an empty string to act as template 
that will be overwritten in the secret file.

### Secret values
Environment specific values are in `app/env.php`.    
This file should be added to the `.gitignore` file to not be pushed accidentally.   
It has security and efficiency (protects against overwriting at deployment) advantages to store the 
`env.php` file right above the project directory. 
In this case the file would be in `app/../../env.php`.

### Env values for integration testing
The environment values for integration testing like the database name are stored inside 
`env.testing.php` which is included last in `settings.php` when `APP_ENV` is set to `'testing'`.

### Combination / final file
`app/settings.php` is the main configuration file that will be used in the project since 
it combines the default and env settings.   
They are loaded in this order:  
1. File `app/defaults.php`
2. File `app/env.php` or `app/../../env.php`
3. If the constant `APP_ENV` is defined, the environment specific file is loaded. 
This is only used to apply the phpunit test settings. Defined in `tests/bootstrap.php`.


### Different files for non-secret env specific values?
To have the cleanest solution `env.php` would only contain secret values and non-secret 
environment values like the database name or display error details bool would be stored in 
`env.development.php` and `env.production.php` (`env.testing.php` is different and has to exist anyways). 
They would then be included at the beginning of `env.php` with `require __DIR__ . '/env.development.php';`.  
Now I decided to still store the env specific non-secret values `env.php` because:
1. There are not enough of those values in question that I consider it really beneficial
2. It adds some complexity since there are 2 more files in the `app/` directory, and it might be harder 
to understand where which config values are stored.  

Now there are some advantages I am missing out on like 
* Being able to change for instance the database name without having to modify the remote `env.php` 
simply by changing `env.production.php` and deploy it.
* It's easier for example purposes. For this project I have to have `env.prod-example.php` and 
`env.dev-example.php`. 

*This is not a final decision though, and I might very well change my mind if for instance I need to give access 
to env specific non secret values to some service (maybe github actions or a testing / linting tool).*
