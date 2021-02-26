<?php

Route::group(['middleware' => ['auth']], function () {
    Route::post('/', 'MediaController@storeNew')->name('medias.create');
});
