<?php

namespace MarcinKozak\DatabaseMigrator\Commands;

use MarcinKozak\DatabaseMigrator\Contracts\TableMigrateContract;
use MarcinKozak\DatabaseMigrator\MigratorManager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\LogicException;
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
     * @return mixed
     */
    public function handle() {
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
     * @throws LogicException
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
