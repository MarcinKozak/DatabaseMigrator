<?php

use MarcinKozak\DatabaseMigrator\Schema;
use MarcinKozak\DatabaseMigrator\Mappers\Table;
use MarcinKozak\DatabaseMigrator\Mappers\Column;

class ExampleSchema extends Schema {

    public function setUp() : void {
        $table = new Table('kittens', 'cats');
        $table->schema([
            'kitten_id' => 'id',
            'kitten_name' => 'name',
            'ordering' => 'sort_order',
        ]);

        $this->migrator->addTable($table);

        $colorColumn = new Column('color', 'colour');
        $colorColumn->map(static function ($value) {
            return '#' . $value;
        });

        $table->addColumn($colorColumn);

        $this->migrator->addTable($table);
    }

}