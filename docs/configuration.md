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
It has security and efficiency (simplifies deployment) advantages to store the 
`env.php` file right above the project directory. 
In this case the file would be in `app/../../env.php`.

### Combination / final file
`app/settings.php` is the main configuration file that will be used in the project since 
it combines the default and env settings.   
They are loaded in this order:  
1. File `app/defaults.php`
2. File `app/env.php` or `app/../../env.php`
3. If the constant `APP_ENV` is defined, the environment specific file is loaded. 
This is only used to apply the phpunit test settings. Defined in `tests/bootstrap.php`.