<?php

Route::get('', 'JoryController@multiple')->name('jory.multiple');

// Routes by resource
Route::get('/{resource}/count', 'JoryController@count')->name('jory.count');
Route::get('/{resource}/first', 'JoryController@first')->name('jory.first');
Route::get('/{resource}/{id}', 'JoryController@show')->name('jory.show');
Route::get('/{resource}', 'JoryController@index')->name('jory.index');
