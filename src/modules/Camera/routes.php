<?php

Route::group(['namespace' => 'Modules\Camera\Controllers'], function () {
    Route::get('/', 'CameraController@index')->name('cameras');

    Route::post('/', 'CameraController@store')->name('cameras.store');

    Route::put('/{id}', 'CameraController@update')->name('cameras.edit');

    Route::delete('/{id}', 'CameraController@delete')->name('cameras.delete');
});
