<?php

namespace MarcinKozak\DatabaseMigrator;

use MarcinKozak\DatabaseMigrator\Contracts\TableMigrateContract;
use MarcinKozak\DatabaseMigrator\Exceptions\MigrationException;
use MarcinKozak\DatabaseMigrator\Mappers\Table;
use Illuminate\Database\Connection;

class Migrator {

    /**
     * @var Connection
     */
    protected $sourceConn;

    /**
     * @var Connection
     */
    protected $targetConn;

    /**
     * @var array|Table[]
     */
    protected $tables = [];

    /**
     * @var int
     */
    protected $chunkSize = 150;

    /**
     * Migrator constructor.
     * @param Connection $sourceConn
     * @param Connection $targetConn
     */
    public function __construct(Connection $sourceConn, Connection $targetConn) {
        $this->sourceConn = $sourceConn;
        $this->targetConn = $targetConn;
    }

    /**
     * @param Table $table
     */
    public function addTable(Table $table) {
        $this->tables[] = $table;
    }

    /**
     * Run the migration process for all added tables.
     *
     * @param TableMigrateContract $handler
     */
    public function migrateTables(TableMigrateContract $handler) {
        foreach($this->tables as $table) {
            $this->migrateTable($handler, $table);
        }
    }

    /**
     * Run the migration process for the given table.
     *
     * @param TableMigrateContract $handler
     * @param Table $table
     * @throws MigrationException
     */
    public function migrateTable(TableMigrateContract $handler, Table $table) {
        $handler->beginTransaction('Migration: [' . $table->getSourceTable() . '] -> [' . $table->getTargetTable() . ']');

        $count = $this->sourceConn
            ->table($table->getSourceTable())
            ->count();

        $affectedRows = 0;

        $handler->notify('Rows number: ' . $count);
        $this->targetConn->beginTransaction();

        try {
            $this->sourceConn
                ->table($table->getSourceTable())
                ->select($table->getSourceColumnNames())
                ->chunk($this->chunkSize, function(array $rows) use($table, & $affectedRows, $count, $handler) {
                    $inserts        = $table->transform($rows);
                    $affectedRows   += count($rows);
                    $progress       = round($affectedRows / $count * 100, 2);

                    $this->targetConn->table($table->getTargetTable())->insert($inserts);
                    $handler->notify('Affected rows [' . $affectedRows . '] of [' . $count . '] as [' . $progress . '%]');
                });

            $this->targetConn->commit();
            $handler->commit('Success');
        }
        catch(MigrationException $e) {
            $handler->rollback($e->getMessage() . "\n\r" . $e->getTraceAsString());
        }
    }

    /**
     * @param TableMigrateContract $handler
     */
    public function clear(TableMigrateContract $handler) {
        foreach(array_reverse($this->tables) as $table) {
            $this->clearTargetTable($handler, $table);
        }
    }

    /**
     * @param TableMigrateContract $handler
     * @param Table $table
     * @throws MigrationException
     */
    public function clearTargetTable(TableMigrateContract $handler, Table $table) {
        $handler->beginTransaction('Clearing: [' . $table->getTargetTable() . ']');

        $this->targetConn->beginTransaction();

        try {
            $this->targetConn->table($table->getTargetTable())->truncate();

            $this->targetConn->commit();
            $handler->commit('Success');
        }
        catch(MigrationException $e) {
            $handler->rollback($e->getMessage() . "\n\r" . $e->getTraceAsString());
        }
    }

    /**
     * @return int
     */
    public function getTablesCount() {
        return count($this->tables);
    }

    public function disconnect() {
        $this->sourceConn->disconnect();
        $this->targetConn->disconnect();
    }

}
