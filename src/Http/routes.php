<?php

Route::get('', 'JoryController@multiple');
Route::options('', 'JoryController@resourceList');

// Routes by resource
Route::get('/{resource}/count', 'JoryController@count');
Route::get('/{resource}/{id}', 'JoryController@show');
Route::get('/{resource}', 'JoryController@index');
Route::options('/{resource}', 'JoryController@options');
