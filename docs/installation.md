## Installation via Composer

**Step 1:** Create the project:

```bash
composer create-project samuelgfeller/slim-example-project my-project
```

**Step 2:** Setup database

Create a new development database (you may change the db name if you update it in `env.php`)

```bash
mysql -e 'CREATE DATABASE IF NOT EXISTS slim_example_project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
```

Create a new database for integration tests

```bash
mysql -e 'CREATE DATABASE IF NOT EXISTS slim_example_project_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
```

Create tables from `resources/schema/schema.sql`

Add table entries (optional) `resources/fixtures/dev-fixtures.sql`

**Step 3:** Configuration

Rename the file: `app/env.dev-example.php` to `app/env.php`.

Change the connection configuration in `config/env.php`:

```php
// Database
$settings['db']['database'] = 'slim_example_project'; // Your db name
$settings['db']['username'] = 'root';
$settings['db']['password'] = '';
```