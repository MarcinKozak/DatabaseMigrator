<?php

namespace MarcinKozak\DatabaseMigrator\Contracts;

interface TableMigrateContract {

    /**
     * @param string $message
     * @return void
     */
    public function beginTransaction(string $message) : void;

    /**
     * @param string $message
     * @return void
     */
    public function rollback(string $message) : void;

    /**
     * @param string $message
     * @return void
     */
    public function commit(string $message) : void;

    /**
     * @param string $message
     * @return void
     */
    public function notify(string $message) : void;

}
