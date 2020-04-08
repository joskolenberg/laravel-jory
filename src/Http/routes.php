<?php

use Illuminate\Support\Facades\Route;
use JosKolenberg\LaravelJory\Http\Controllers\JoryController;

// Multiple resources
Route::get('', [JoryController::class, 'multiple'])->name('jory.multiple');

// Routes by resource
Route::get('/{resource}/count', [JoryController::class, 'count'])->name('jory.count');
Route::get('/{resource}/exists', [JoryController::class, 'exists'])->name('jory.exists');
Route::get('/{resource}/first', [JoryController::class, 'first'])->name('jory.first');
Route::get('/{resource}/{id}', [JoryController::class, 'find'])->name('jory.find');
Route::get('/{resource}', [JoryController::class, 'get'])->name('jory.get');
