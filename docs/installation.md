# Installation guide

In order to install and run this project, you need to have PHP, Composer, and a MariaDB or MySQL server 
installed and running on your machine.

### 1. Create project

Navigate to the directory you want to create the project in and run 
the following command, replacing `[project-name]` with the desired name for your project:
```bash
composer create-project samuelgfeller/slim-example-project [project-name]
```
This will create a new directory with the specified name and install all necessary dependencies.

Alternatively, you can use GitHub's 
[Use this template](https://docs.github.com/en/repositories/creating-and-managing-repositories/creating-a-repository-from-a-template)
feature to quickly create a repository with the code of this project. 
Checkout this repository in your preferred IDE before proceeding.

### 2. Set up the database
After opening the project in your IDE, rename the file `config/env/env.example.php` to `env.php` 
and fill in your database credentials.  

Then, create your database and update the `config/env/env.dev.php` file with the name of your 
database, like this:
```php
$settings['db']['database'] = 'my_database_name';
```
After that, create a separate test database and update the `config/env/env.test.php` file with its
name. The name must contain the word "test" as a safety measure to prevent accidentally truncating 
the development database:
```php
$settings['db']['database'] = 'my_database_name_test';
```

### 3. Run migrations
Open the terminal in the project's root directory and run the following command to create the necessary 
tables for the project:
```bash
composer migrate
```

### 4. Insert data
You can choose to insert only the minimal amount of data required for the app to function, or also 
include some dummy example data.

To insert both minimal and dummy data, run:
```bash
composer seed
```

To insert only the minimal data, run:
```bash
composer seed:minimal
```

### 5. Update GitHub workflows

**Deployment**   
If you are not planning on 
deploying your app at this time, delete or comment out the contents of the 
`.github/workflows/master.yml` file.  
  
To deploy your app, update the `.github/workflows/master.yml` file according to your needs and 
add your server's credentials to GitHub's 
[Actions secrets](https://docs.github.com/en/actions/security-guides/encrypted-secrets).

**Build testing**   
To run the project's tests automatically when pushing, update the 
`.github/workflows/develop.yml` file.   
**Replace the matrix value "test-database" `slim_example_project_test` with the name of 
your test database** as specified in `config/env/env.test.php`.
If you are not using SonarCloud, remove the "SonarCloud Scan" step from the workflow.

### Done!
That's it! Your project should now be fully set up and ready to use.  
You can serve it locally by running `php -S localhost:8080 -t public/` in the project's root 
directory and share it on a version control such as GitHub.