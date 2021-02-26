<?php

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', 'UserController@index')->name('users');
    
    Route::get('/create', 'UserController@create')->name('users.create');

    Route::post('/create', 'UserController@storeNew')->name('users.store');
});
