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
    public function getMigrator() : Migrator {
        return $this->migrator;
    }

    abstract public function setUp() : void;

}