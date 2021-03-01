<?php

Route::group(['middleware' => ['auth']], function () {
    Route::post('/', 'MediaController@storeNew')->name('medias.create');

    Route::post('/thumbnails', 'MediaController@createThumbnail')->name('medias.thumbnail.create');
});
