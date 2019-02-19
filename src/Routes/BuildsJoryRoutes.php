<?php

namespace JosKolenberg\LaravelJory\Routes;

use Illuminate\Support\Facades\Route;

trait BuildsJoryRoutes
{
    public static function routes(string $baseUri = '')
    {
        // Base routes
        Route::get($baseUri, '\\'.JoryController::class.'@multiple');
        Route::options($baseUri, '\\'.JoryController::class.'@resourceList');

        // Routes by resource
        Route::get($baseUri.'/{resource}/count', '\\'.JoryController::class.'@count');
        Route::get($baseUri.'/{resource}/{id}', '\\'.JoryController::class.'@show');
        Route::get($baseUri.'/{resource}', '\\'.JoryController::class.'@index');
        Route::options($baseUri.'/{resource}', '\\'.JoryController::class.'@options');
    }
}
