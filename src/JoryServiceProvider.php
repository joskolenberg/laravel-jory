<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Support\ServiceProvider;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;

class JoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/jory.php' => config_path('jory.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jory.php', 'jory');

        $this->app->singleton(JoryBuildersRegister::class, function () {
            return new JoryBuildersRegister();
        });
    }
}
