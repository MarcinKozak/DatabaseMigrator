<?php

namespace MarcinKozak\DatabaseMigrator;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\DatabaseManager;
use MarcinKozak\DatabaseMigrator\Exceptions\MigrationException;

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
     * @throws MigrationException
     */
    public function all() : array {
        $connections    = (array) $this->config->get('marcinkozak.databasemigrator.connections', []);
        $buildSchemas   = [];

        foreach($connections as $connection) {
            if($connection['enabled'] ?? false) {
                $schemaClassName = $connection['schema'] ?? null;

                if($schemaClassName) {
                    $sourceConn = $this->databaseManager->connection($connection['source']);
                    $targetConn = $this->databaseManager->connection($connection['target']);

                    $schema = new $schemaClassName(new Migrator($sourceConn, $targetConn));

                    if($schema instanceof Schema) {
                        $schema->setUp();

                        $buildSchemas[] = $schema;
                    }
                    else {
                        $message = sprintf('The schema [%s] is not instance of [%s]', $schema, Schema::class);
                        throw new MigrationException($message);
                    }

                }
            }
        }

        return $buildSchemas;
    }

}
