<?php

namespace JosKolenberg\LaravelJory\Routes;

use Illuminate\Support\Facades\Route;

trait BuildsJoryRoutes
{
    public static function routes(string $baseUri = '')
    {
        Route::get($baseUri.'/{uri}/{id}', JoryController::class.'@show');
        Route::get($baseUri.'/{uri}', JoryController::class.'@index');
    }
}
