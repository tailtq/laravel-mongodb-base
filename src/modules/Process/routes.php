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

    Route::get('/{id}/detail', 'ProcessController@getDetailAfterSuccessOrStop')->name('processes.durations');

    Route::get('/{id}/export/before-grouping', 'ProcessController@exportBeforeGrouping')->name('processes.export.before-grouping');

    Route::get('/{id}/export/after-grouping', 'ProcessController@exportAfterGrouping')->name('processes.export.after-grouping');

    Route::post('/search-faces', 'ProcessController@searchFace')->name('processes.search-face');
});
