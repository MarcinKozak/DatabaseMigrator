<?php

namespace MarcinKozak\DatabaseMigrator;

use Exception;
use Illuminate\Database\Connection;
use MarcinKozak\DatabaseMigrator\Contracts\TableMigrateContract;
use MarcinKozak\DatabaseMigrator\Exceptions\MigrationException;
use MarcinKozak\DatabaseMigrator\Mappers\Table;

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
    public function addTable(Table $table) : void {
        $this->tables[] = $table;
    }

    /**
     * Run the migration process for all added tables.
     *
     * @param TableMigrateContract $handler
     * @throws MigrationException
     */
    public function migrateTables(TableMigrateContract $handler) : void {
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
    public function migrateTable(TableMigrateContract $handler, Table $table) : void {
        $handler->beginTransaction('Migration: [' . $table->getSourceTable() . '] -> [' . $table->getTargetTable() . ']');

        $count = $this->sourceConn
            ->table($table->getSourceTable())
            ->count();

        $affectedRows   = 0;
        $charsetIn      = $this->sourceConn->getConfig('charset');
        $charsetOut     = $this->targetConn->getConfig('charset');

        $handler->notify('Rows number: ' . $count);

        try {
            $this->targetConn->beginTransaction();

            $this->sourceConn
                ->table($table->getSourceTable())
                ->select($table->getSourceColumnNames())
                ->chunk($this->chunkSize, function(array $rows) use($table, & $affectedRows, $count, $handler, $charsetIn, $charsetOut) {
                    $inserts        = $table->transform($rows, $charsetIn, $charsetOut);
                    $affectedRows   += count($rows);
                    $progress       = round($affectedRows / $count * 100, 2);

                    $this->targetConn->table($table->getTargetTable())->insert($inserts);
                    $handler->notify('Affected rows [' . $affectedRows . '] of [' . $count . '] as [' . $progress . '%]');
                });

            $this->targetConn->commit();
            $handler->commit('Success');
        }
        catch(Exception $e) {
            $handler->rollback($e->getMessage() . "\n\r" . $e->getTraceAsString());

            throw new MigrationException($e->getMessage());
        }
    }

    /**
     * @param TableMigrateContract $handler
     * @throws MigrationException
     */
    public function clear(TableMigrateContract $handler) : void {
        foreach(array_reverse($this->tables) as $table) {
            $this->clearTargetTable($handler, $table);
        }
    }

    /**
     * @param TableMigrateContract $handler
     * @param Table $table
     * @throws MigrationException
     */
    public function clearTargetTable(TableMigrateContract $handler, Table $table) : void {
        $handler->beginTransaction('Clearing: [' . $table->getTargetTable() . ']');

        try {
            $this->targetConn->beginTransaction();
            $this->targetConn->table($table->getTargetTable())->truncate();

            $this->targetConn->commit();
            $handler->commit('Success');
        }
        catch(Exception $e) {
            $handler->rollback($e->getMessage() . "\n\r" . $e->getTraceAsString());

            throw new MigrationException($e->getMessage());
        }
    }

    /**
     * @return int
     */
    public function getTablesCount() : int {
        return count($this->tables);
    }

    public function disconnect() : void {
        $this->sourceConn->disconnect();
        $this->targetConn->disconnect();
    }

}
