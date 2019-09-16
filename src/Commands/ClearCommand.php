<?php

namespace MarcinKozak\DatabaseMigrator\Commands;

use Illuminate\Console\Command;
use MarcinKozak\DatabaseMigrator\Contracts\TableMigrateContract;
use MarcinKozak\DatabaseMigrator\Exceptions\MigrationException;
use MarcinKozak\DatabaseMigrator\MigratorManager;
use Symfony\Component\Console\Helper\ProgressBar;

class ClearCommand extends Command implements TableMigrateContract {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database-migrator:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear migrations.';

    /**
     * @var MigratorManager
     */
    protected $migratorManager;

    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * ClearCommand constructor.
     * @param MigratorManager $migratorManager
     */
    public function __construct(MigratorManager $migratorManager) {
        parent::__construct();

        $this->migratorManager = $migratorManager;
    }

    /**
     * Execute the console command.
     *
     * @throws MigrationException
     */
    public function handle() : void {
        $schemas = $this->migratorManager->all();

        foreach($schemas as $schema) {
            $migrator = $schema->getMigrator();
            $this->progressBar = $this->output->createProgressBar($migrator->getTablesCount());
            $migrator->clear($this);

            $migrator->disconnect();
            $this->progressBar->finish();
        }
    }

    /**
     * @param string $message
     */
    public function beginTransaction(string $message) : void {
        $this->info($message);
    }

    /**
     * @param string $message
     */
    public function rollback(string $message) : void {
        $this->error($message);
    }

    /**
     * @param string $message
     */
    public function commit(string $message) : void {
        $this->info($message);

        $this->progressBar->advance();
    }

    /**
     * @param string $message
     */
    public function notify(string $message) : void {
        $this->info($message);
    }

}
