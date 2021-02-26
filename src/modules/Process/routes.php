<?php

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', 'ProcessController@index')->name('processes');
    
    Route::get('/create', 'ProcessController@index')->name('processes');

    Route::post('/create', 'ProcessController@store')->name('processes.store');

    Route::put('/{id}', 'ProcessController@update')->name('processes.edit');

    Route::delete('/{id}', 'ProcessController@delete')->name('processes.delete');
});
