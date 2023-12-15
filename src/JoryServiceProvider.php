<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\Config\Validator;
use JosKolenberg\LaravelJory\Console\JoryResourceGenerateCommand;
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
                JoryResourceGenerateCommand::class,
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

        $this->app->bind(Validator::class, function ($app, $params){
            return new Validator($params['config'], $params['jory']);
        });

        $this->app->bind(Config::class, function ($app, $params){
            return new Config($params['modelClass']);
        });
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes(): void
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
    private function routeConfiguration(): array
    {
        return [
            'prefix' => config('jory.routes.path'),
            'middleware' => 'jory',
        ];
    }

}
