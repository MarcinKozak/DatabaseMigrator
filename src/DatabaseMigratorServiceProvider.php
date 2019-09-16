<?php

namespace MarcinKozak\DatabaseMigrator;

use Illuminate\Support\ServiceProvider;
use MarcinKozak\DatabaseMigrator\Commands\ClearCommand;
use MarcinKozak\DatabaseMigrator\Commands\PopulateCommand;

class DatabaseMigratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() : void  {
        $this->publishes([
            __DIR__.'/../config' => config_path('marcinkozak/databasemigrator'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/schemas/' => database_path('schemas')
        ], 'schemas');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() : void {
        $this->commands(PopulateCommand::class);
        $this->commands(ClearCommand::class);
    }

}
