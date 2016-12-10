<?php

namespace MarcinKozak\DatabaseMigrator;

use Illuminate\Container\Container;
use Illuminate\Database\DatabaseManager;
use InvalidArgumentException;
use Illuminate\Contracts\Config\Repository as Config;

class MigratorManager {

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * MigratorManager constructor.
     * @param Container $container
     * @param Config $config
     * @param DatabaseManager $databaseManager
     */
    public function __construct(Container $container, Config $config, DatabaseManager $databaseManager) {
        $this->container = $container;
        $this->config = $config;
        $this->databaseManager = $databaseManager;
    }

    /**
     * @return Schema[]
     */
    public function all() {
        $connections    = (array) $this->config->get('marcinkozak.databasemigrator.connections', []);
        $buildSchemas   = [];

        foreach($connections as $connection) {
            if(array_get($connection, 'enabled')) {
                $sourceConn         = $this->databaseManager->connection($connection['source']);
                $targetConn         = $this->databaseManager->connection($connection['target']);
                $migrator           = new Migrator($sourceConn, $targetConn);
                $schemaClassName    = array_get($connection, 'schema');

                if($schemaClassName) {
                    $schema = new $schemaClassName($migrator);
                    $schema->setUp();

                    $buildSchemas[] = $schema;
                }
            }
        }

        return $buildSchemas;
    }

}
