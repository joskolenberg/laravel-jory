<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Support\ServiceProvider;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;
use JosKolenberg\LaravelJory\Console\JoryBuilderMakeCommand;
use JosKolenberg\LaravelJory\Register\ManualRegistrar;

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
            $register = new JoryBuildersRegister(new ManualRegistrar());

            foreach (config('jory.registrars') as $registrar){
                $register->addRegistrar(new $registrar());
            }

            return $register;
        });

        $this->app->singleton(CaseManager::class, function ($app) {
            return new CaseManager($app->make('request'));
        });
    }
}
