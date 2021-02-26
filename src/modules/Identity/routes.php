<?php

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', 'IdentityController@index')->name('identities');
    
    Route::get('/create', 'IdentityController@create')->name('identities.create');

    Route::post('/create', 'IdentityController@storeNew')->name('identities.store');

    Route::get('/{id}', 'IdentityController@edit')->name('identities.edit');

    Route::put('/{id}', 'IdentityController@updateNew')->name('identities.update');

    Route::delete('/{id}', 'IdentityController@delete')->name('identities.delete');
});
