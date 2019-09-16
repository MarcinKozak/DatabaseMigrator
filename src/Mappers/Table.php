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
    public function __construct(string $sourceTableName, string $targetTableName = null) {
        $this->sourceTableName = $sourceTableName;
        $this->targetTableName = $targetTableName ?? $sourceTableName;
    }

    /**
     * @param Column $column
     * @return Table
     */
    public function addColumn(Column $column) : self {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceTable() : string {
        return $this->sourceTableName;
    }

    /**
     * @return string
     */
    public function getTargetTable() : string {
        return $this->targetTableName;
    }

    /**
     * @return Column[]
     */
    public function getColumns() : array {
        return $this->columns;
    }

    /**
     * @param array $columnsMap
     */
    public function schema(array $columnsMap) : void {
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
    public function getSourceColumnNames() : array {
        $names = array_map(static function(Column $column) {
            return $column->getName();
        }, $this->columns);

        return array_unique($names);
    }

    /**
     * @return string[]
     */
    public function getTargetColumnNames() : array {
        $names = array_map(static function(Column $column) {
            return $column->getNewName();
        }, $this->columns);

        return array_unique($names);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasSourceColumn(string $name) : bool {
        return in_array($name, $this->getSourceColumnNames(), true);
    }

    /**
     * @param string $name
     * @return Column[]
     */
    public function getNewColumnNameBySource(string $name) : array {
        if( ! $this->hasSourceColumn($name) ) {
            return [];
        }

        return array_filter($this->columns, static function(Column $column) use($name) {
            return $column->getName() === $name;
        });
    }

    /**
     * @param stdClass[] $rows
     * @param string $charsetIn
     * @param string $charsetOut
     * @return array
     */
    public function transform(array $rows, string $charsetIn, string $charsetOut) : array {
        $newRows = [];

        foreach($rows as $row) {
            $newRows[] = $this->transformRow( (array) $row, $charsetIn, $charsetOut);
        }

        return $newRows;
    }

    /**
     * @param array $row
     * @param string $charsetIn
     * @param string $charsetOut
     * @return array
     */
    protected function transformRow(array $row, string $charsetIn, string $charsetOut) : array {
        $newRow = [];

        foreach($row as $columnName => $value) {
            $columns = $this->getNewColumnNameBySource($columnName);

            foreach($columns as $column) {
                $newValue       = $column->getValue($value);
                $encodedValue   = iconv($charsetIn, $charsetOut, $newValue);

                $newRow[ $column->getNewName() ] = $encodedValue;
            }
        }

        return $newRow;
    }

}
