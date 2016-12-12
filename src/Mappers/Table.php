<?php

namespace MarcinKozak\DatabaseMigrator\Mappers;

use stdClass;

class Table {

    /**
     * @var string
     */
    protected $sourceTableName;

    /**
     * @var string
     */
    protected $targetTableName;

    /**
     * @var Column[]
     */
    protected $columns = [];

    /**
     * Table constructor.
     * @param string $sourceTableName
     * @param string|null $targetTableName
     */
    public function __construct($sourceTableName, $targetTableName = null) {
        $this->sourceTableName = $sourceTableName;
        $this->targetTableName = ($targetTableName === null) ? $sourceTableName : $targetTableName;
    }

    /**
     * @param Column $column
     * @return Table
     */
    public function addColumn(Column $column){
        $this->columns[] = $column;

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceTable() {
        return $this->sourceTableName;
    }

    /**
     * @return string
     */
    public function getTargetTable() {
        return $this->targetTableName;
    }

    /**
     * @return Column[]
     */
    public function getColumns() {
        return $this->columns;
    }

    /**
     * @param array $columnsMap
     */
    public function schema(array $columnsMap) {
        foreach($columnsMap as $sourceColumn => $targetColumn) {
            if(is_int($sourceColumn)) {
                $sourceColumn = $targetColumn;
            }

            $this->addColumn(new Column($sourceColumn, $targetColumn));
        }
    }

    /**
     * @return string[]
     */
    public function getSourceColumnNames() {
        $names = array_map(function(Column $column) {
            return $column->getName();
        }, $this->columns);

        return array_unique($names);
    }

    /**
     * @return string[]
     */
    public function getTargetColumnNames() {
        $names = array_map(function(Column $column) {
            return $column->getNewName();
        }, $this->columns);

        return array_unique($names);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasSourceColumn($name) {
        return in_array($name, $this->getSourceColumnNames(), true);
    }

    /**
     * @param string $name
     * @return Column[]
     */
    public function getNewColumnNameBySource($name) {
        if( ! $this->hasSourceColumn($name) ) {
            return [];
        }

        return array_filter($this->columns, function(Column $column) use($name) {
            return $column->getName() === $name;
        });
    }

    /**
     * @param stdClass[] $rows
     * @return array
     */
    public function transform(array $rows) {
        $newRows = [];

        foreach($rows as $row) {
            $newRows[] = $this->transformRow( (array) $row);
        }

        return $newRows;
    }

    /**
     * @param array $row
     * @return array
     */
    protected function transformRow(array $row) {
        $newRow = [];

        foreach($row as $columnName => $value) {
            $columns = $this->getNewColumnNameBySource($columnName);

            foreach($columns as $column) {
                $newRow[ $column->getNewName() ] = $column->getValue($value);
            }
        }

        return $newRow;
    }

}
