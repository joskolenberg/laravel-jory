<?php

use JosKolenberg\LaravelJory\Http\Controllers\JoryController;

// Multiple resources
Route::get('', [JoryController::class, 'multiple'])->name('jory.multiple');

// Routes by resource
Route::get('/{resource}/count', [JoryController::class, 'count'])->name('jory.count');
Route::get('/{resource}/first', [JoryController::class, 'first'])->name('jory.first');
Route::get('/{resource}/{id}', [JoryController::class, 'show'])->name('jory.show');
Route::get('/{resource}', [JoryController::class, 'index'])->name('jory.index');
