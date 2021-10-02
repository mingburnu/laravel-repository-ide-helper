<?php

namespace Mingburnu\RepositoryIdeHelper;

use Illuminate\Support\ServiceProvider;

class RepositoryIdeHelperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->registerCommands();
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\GenerateCommand::class,
            ]);
        }
    }
}