<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Support\ServiceProvider;

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
    }
}
