<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;
use JosKolenberg\LaravelJory\Console\JoryBuilderMakeCommand;
use JosKolenberg\LaravelJory\Register\ManualRegistrar;

class JoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::middlewareGroup('jory', config('jory.routes.middleware', []));

        $this->publishes([
            __DIR__.'/../config/jory.php' => config_path('jory.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                JoryBuilderMakeCommand::class,
            ]);
        }

        $this->registerRoutes();
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

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        if(config('jory.routes.enabled')){
            Route::group($this->routeConfiguration(), function () {
                $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
            });
        }
    }

    /**
     * Get the Jory route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'namespace' => 'JosKolenberg\LaravelJory\Http\Controllers',
            'prefix' => config('jory.routes.path'),
            'middleware' => 'jory',
        ];
    }

}
