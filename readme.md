# Basic migrations

[![HLEB2](https://img.shields.io/badge/HLEB-2-darkcyan)](https://github.com/phphleb/hleb) ![PHP](https://img.shields.io/badge/PHP-^8.2-blue) [![License: MIT](https://img.shields.io/badge/License-MIT%20(Free)-brightgreen.svg)](https://github.com/phphleb/hleb/blob/master/LICENSE)

Basic migrations for the [HLEB2](https://github.com/phphleb/hleb) PHP framework.


These migrations implement a minimal set of standard actions, their main purpose is to execute prepared queries and record their execution in the database, preventing repetition.
To achieve this, the framework must be connected to a database.

Support for __MySQL__ / __MariaDB__ / __PostgreSQL__

#### Installation
```bash
composer require phphleb/migration
```
#### Deployment (when using the HLEB2 framework)
```bash
php console phphleb/migration add
```

#### Usage
Next, you can create a migration template using the console command:
```bash
php console migration/create example_name
```

This command will create a new migration in the project's `database/migrations` (or migrations if it doesn't exist) folder with the name 'MigrationXXXexamplename.php' (where XXX index is the current UNIX time in milliseconds). If the `database/migrations` or `migrations` folder is not in the project root directory, you need to create it manually. If this folder is not suitable for you, you will need to rewrite the console command classes specifying the directory and use your own; deployment of the library in this case is optional. However, if a single database is used, these two location options should suffice (only one of them should be used).

In the 'MigrationXXXexamplename' class of the migration file, there is an up() method where one or more SQL queries can be added using the **addS**ql(...) method. Similarly, the **down**() method contains queries that roll back the **up**() method's execution. Be cautious with rolling back migrations, many do not use it, but if it is mandatory for the project, you must keep it up to date.

```php
<?php

class Migration_XXX_example_name extends \Phphleb\Migration\Src\StandardMigration
{
   public function up(PDO $db)
   {
      $this->addSql("CREATE TABLE table_name (cell_id int)");
   }

   public function down()
   {
      $this->addSql("DROP TABLE table_name");
   }

}


```
An important feature of the **up** method is the obligatory presence of at least one executable action $this->addSql(...);

There is also a command to run migrations:

```bash
php console migration/run --no-notify
```

It will execute all unrecorded migrations (previously not executed) and record them in the migrations table of the database, which is specified in the project settings (DB configuration) as the main one. Accordingly, all migration SQL queries can only be executed on this database. If you remove `--no-notify`, the command execution details will be displayed.

For a secondary database from the framework's configuration, you can also additionally create your own console commands (create, rollback, run, and status) specifying the specific connection name from the DB configuration. For example: `new Migration(DB::getPdoInstance("mysql.other-name"), "other-migrations-table-name", "other/migrations/path")`. If there are several options, you can pass them as an argument without creating separate console commands for each. For different connection options, you need to specify different storage tables and folders, as in the example.

Before running migrations, this command will help to clarify the list of unrecorded migrations in the project without performing the migrations themselves:

```bash
php console migration/status
```

The following command is intended for rolling back migrations. If queries from the **up**() method were executed during execution, then queries from the **down**() method will now be executed. The order of execution of migrations will be reversed, i.e., the last recorded one will be executed first, then the second-to-last, and so on. If a number is specified as a command argument, the rollback will occur for that number of migrations. By default (without an argument), it will roll back one migration. If you need to roll back all migrations, you need to specify 'all' as an argument, and it will be applied to all existing migrations with the cleaning of the corresponding table in the database.

```bash
php console migration/rollback all --no-notify
```

When this command is executed, all migrations will be rolled back. Counting starts from the recorded migrations that have already been executed. If you need to roll back only a few migrations, for example, the last two:


```bash
php console migration/rollback 2
```


_If you want to add migrations to an already existing project with a database of a certain size, create the first migration from the dump of this DB's structure, adding the `IF NOT EXISTS` expression to the creation of tables, and then execute the migrations._

#### Arbitrary Setup

There is an option to use this migration mechanism outside the HLEB2 framework, in any PHP project. The connection principle can be found in the console commands of this library; the connection is implemented there as follows:


```php
use Hleb\Static\DB;
use Phphleb\Migration\Src\Migration;

(new Migration(DB::getPdoInstance(), 'migrations', '/path/to/migrations/'))->run();

```

where the first argument in the Migration class constructor is the initialized PDO object, the second is the name of the table for storing migration data (default is 'migrations'), and the third is the full path to the folder for storing migration files. It is sufficient to implement similar console commands with your own arguments substituted.

#### Update

```bash
composer update phphleb/migration

php console phphleb/migration add

composer dump-autoload
```
