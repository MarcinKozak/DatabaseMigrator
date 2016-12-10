# Database Migrator for Laravel

A simple console tool which help to migration database data from one to another using different DB connections.

### Features

- Defining source and target database connection.
- Allows to change target table name and columns.
- Transforms data for each processing rows.

### Installation

Add `marcinkozak/databasemigrator` to `composer.json`.
```
"marcinkozak/databasemigrator": "dev-master"
```
Or simply run:
```
composer require marcinkozak/databasemigrator
```

To make it available for Laravel open the ```config/app.php``` file and add below line.

```php
'providers' => array(

    // Other service provider lines
    
    MarcinKozak\DatabaseMigrator\DatabaseMigratorServiceProvider::class,
);
```

### Configuration

TODO

### Examples

TODO