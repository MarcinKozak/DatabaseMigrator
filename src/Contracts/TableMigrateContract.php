<?php

namespace MarcinKozak\DatabaseMigrator\Contracts;

interface TableMigrateContract {

    /**
     * @param string $message
     * @return mixed
     */
    public function beginTransaction($message);

    /**
     * @param string $message
     * @return mixed
     */
    public function rollback($message);

    /**
     * @param string $message
     * @return mixed
     */
    public function commit($message);

    /**
     * @param string $message
     * @return mixed
     */
    public function notify($message);

}
