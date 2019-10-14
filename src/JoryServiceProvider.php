<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use JosKolenberg\LaravelJory\Console\JoryPublishCommand;
use JosKolenberg\LaravelJory\Console\JoryResourceGenerateAllCommand;
use JosKolenberg\LaravelJory\Console\JoryResourceGenerateForCommand;
use JosKolenberg\LaravelJory\Console\JoryResourceMakeCommand;
use JosKolenberg\LaravelJory\Helpers\CaseManager;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;

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
                JoryResourceMakeCommand::class,
                JoryResourceGenerateForCommand::class,
                JoryResourceGenerateAllCommand::class,
                JoryPublishCommand::class,
            ]);
        }

        $this->registerRoutes();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jory.php', 'jory');

        $this->app->singleton(JoryResourcesRegister::class, function () {
            $register = new JoryResourcesRegister();

            foreach (config('jory.registrars') as $registrar){
                $register->addRegistrar(new $registrar());
            }

            return $register;
        });

        $this->app->singleton(CaseManager::class, function ($app) {
            return new CaseManager($app->make('request'));
        });

        $this->app->singleton('jory', function ($app) {
            return new JoryManager();
        });

        $this->app->bind(JoryBuilder::class, function ($app, $params){
            return new JoryBuilder($params['joryResource']);
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
