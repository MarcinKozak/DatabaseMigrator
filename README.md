# Database Migrator for Laravel

A simple console tool which helps to migrate database data from one to another using different DB connections.

### Features

- Defining source and target database connection.
- Allows to change target tables names and columns as well.
- Transforms data for each processing rows.

### When to use

Sometimes you can have an opportunity to make your custom database based on the existing one but in your opinion you can design it in better way for example primary keys are not names simply as ```id``` but in the ```<table_name>_id pattern```, or columns values are not exactly what you want.

### Installation

Add `marcinkozak/databasemigrator` to `composer.json`.
```
"marcinkozak/databasemigrator": "dev-master"
```
Or simply run:
```
composer require marcinkozak/databasemigrator
```

To make it available for Laravel open the ```config/app.php``` file and add line below.

```php
'providers' => array(

    // Other service providers entries
    
    MarcinKozak\DatabaseMigrator\DatabaseMigratorServiceProvider::class,
);
```

### Configuration

Run command

```php
php artisan vendor:publish --provider="MarcinKozak\DatabaseMigrator\DatabaseMigratorServiceProvider"
```

to publish new files in an application root:

- ```config/marcinkozak/databasemigrator/connections.php```
- ```database/schemas/ExampleSchema.php```

The first file contains a collection of connections where each of them defines source and target DB connection, disabled/enabled state and class name of schema. 

```php
<?php

return [
    [
        'source'    => 'mysql2',
        'target'    => 'mysql',
        'enabled'   => true,
        'schema'    => ExampleSchema::class,
    ],
];
```

The schema files are located in the ```database/schemas/``` directory.

Before you start migrating you must have defined target tables, otherwise an exception will be thrown.

### Examples


##### Migrating the same table name

```php
$table = new Table('table_name');
```

##### Migrating different table names

```php
$table = new Table('source_table_name', 'target_table_name');
```

##### Defining columns

This is a mandatory step to make migration works for the selected table. You can define a single column 

```php
$column = new Column('column_name');
$table->addColumn($column);
```

or for different names 

```php
$column = new Column('source_column_name', 'target_column_name');
$table->addColumn($column);
```

or use the ```schema()``` method to define multi columns in single step.

```php
$table->schema([
    'column_1',
    'column_2',
    //...
]);
```

Of course you are able to define different names inside the above method.

```php
$table->schema([
    'source_column_1' => 'target_column_1',
    'source_column_2' => 'target_column_2',
    //...
]);
```

##### Transform column value

Not always we want to migrate the same value for certain column. The package supports the ```map()``` method which allows to transform value to the new one. To use it simply define new ```Column``` instance.

```php
$column = new Column('column_name');
$column->map(function($value) {
    return $value . '_some_stupid_word';
});
```

If you want to make some relation which has poorly designed in the source table you can follow that way 

```php
$data = DB::table('some_table')->pluck('id', 'some_column_name');

$column = new Column('column_name');
$column->map(function($value) use($data) {
    return array_get($data, $value);
});
```

Let's suppose that in this case ```$value``` stores identical value as the ```some_column_name``` column. By using the ```array_get``` function we can fetch the desirable primary key and use it as foreign key for the new value inside the ```map``` method.

### Running migration

The package has two Artisan methods.

##### Populating target tables

```php artisan database-migrator:populate```

##### Clearing target tables

```php artisan database-migrator:clear```

### Known issues

1. While developing this package I have had a task about doing migration DB data from MS SQL server to the MySQL. The source database contains polish chars so I have stuck due wrong characters conversions between databases. Till today I do not know how to solve that.