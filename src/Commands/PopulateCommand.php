<?php

namespace MarcinKozak\DatabaseMigrator\Commands;

use MarcinKozak\DatabaseMigrator\Contracts\TableMigrateContract;
use MarcinKozak\DatabaseMigrator\Migrator;
use MarcinKozak\DatabaseMigrator\MigratorManager;
use MarcinKozak\DatabaseMigrator\Schema;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Symfony\Component\Console\Helper\ProgressBar;

class PopulateCommand extends Command implements TableMigrateContract {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database-migrator:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migration of all defined tables.';

    /**
     * @var MigratorManager
     */
    protected $migratorManager;

    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * PopulateCommand constructor.
     * @param MigratorManager $migratorManager
     */
    public function __construct(MigratorManager $migratorManager) {
        parent::__construct();

        $this->migratorManager = $migratorManager;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $schemas = $this->migratorManager->all();

        foreach($schemas as $schema) {
            $migrator = $schema->getMigrator();
            $this->progressBar = $this->output->createProgressBar($migrator->getTablesCount());
            $migrator->migrateTables($this);

            $migrator->disconnect();
            $this->progressBar->finish();
        }
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function beginTransaction($message) {
        $this->info($message);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function rollback($message) {
        $this->error($message);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function commit($message) {
        $this->info($message);

        $this->progressBar->advance();
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function notify($message) {
        $this->info($message);
    }
}
