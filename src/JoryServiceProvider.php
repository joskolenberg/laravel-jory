<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Support\ServiceProvider;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;
use JosKolenberg\LaravelJory\Console\JoryBuilderMakeCommand;

class JoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/jory.php' => config_path('jory.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                JoryBuilderMakeCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jory.php', 'jory');

        $this->app->singleton(JoryBuildersRegister::class, function () {
            return new JoryBuildersRegister();
        });

        $this->app->singleton(CaseManager::class, function ($app) {
            return new CaseManager($app->make('request'));
        });
    }
}
