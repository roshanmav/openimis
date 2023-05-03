<?php

namespace Insurance\Openimis;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OpenImisServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerRoutes();
        $this->registerCommands();
    }

    /**
     * Register the Insurance routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (Openimis::$registersRoutes) {
            Route::group([
                'as' => 'openimis.',
                'prefix' => '/openimis/',
                'namespace' => 'Insurance\Openimis\Http\Controllers',
            ], function () {
                $path = __DIR__.'/../routes/web.php';
                require $path;
                // $this->loadRoutesFrom();
            });
        }
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/openimis.php', 'openimis');
    }
}
