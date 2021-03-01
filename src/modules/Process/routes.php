<?php

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', 'ProcessController@index')->name('processes');

    Route::get('/{id}', 'ProcessController@show')->name('processes.detail');

    Route::post('/create', 'ProcessController@storeNew')->name('processes.create');

    Route::delete('/{id}', 'ProcessController@delete')->name('processes.delete');

    Route::post('/start-process', 'ProcessController@startProcess')->name('processes.start');

    Route::post('/stop-process', 'ProcessController@stopProcess')->name('processes.stop');

    Route::post('/render-video', 'ProcessController@renderVideo')->name('processes.render');

    Route::get('/{id}/objects', 'ProcessController@getObjects')->name('processes.objects');
});
