<?php

namespace JosKolenberg\LaravelJory;

use Illuminate\Support\ServiceProvider;

class JoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jory.php', 'jory');
    }
}
