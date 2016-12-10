<?php

namespace MarcinKozak\DatabaseMigrator;

abstract class Schema {

    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * Schema constructor.
     * @param Migrator $migrator
     */
    public function __construct(Migrator $migrator) {
        $this->migrator = $migrator;
    }

    /**
     * @return Migrator
     */
    public function getMigrator() {
        return $this->migrator;
    }

    public abstract function setUp();

}