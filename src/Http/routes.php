<?php

Route::get('', 'JoryController@multiple');

// Routes by resource
Route::get('/{resource}/count', 'JoryController@count');
Route::get('/{resource}/{id}', 'JoryController@show');
Route::get('/{resource}', 'JoryController@index');
