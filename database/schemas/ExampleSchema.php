<?php

use MarcinKozak\DatabaseMigrator\Schema;
use MarcinKozak\DatabaseMigrator\Mappers\Table;
use MarcinKozak\DatabaseMigrator\Mappers\Column;

class ExampleSchema extends Schema {

    public function setUp() {
        $table = new Table('kittens', 'cats');
        $table->schema([
            'kitten_id' => 'id',
            'kitten_name' => 'name',
            'ordering' => 'sort_order',
        ]);

        $this->migrator->addTable($table);

        $colorColumn = new Column('color', 'colour');
        $colorColumn->map(function ($value) {
            return '#' . $value;
        });

        $table->addColumn($colorColumn);

        $this->migrator->addTable($table);
    }

}