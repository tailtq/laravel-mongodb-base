<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Application layer


Auth::routes(['register' => false]);

Route::group(['middleware' => ['auth']], function () {

    Route::get('/', 'DashboardController@index')->name('dashboard.index');

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', 'UserController@list')->name('users');

        Route::get('/create', 'UserController@create')->name('users.create');

        Route::post('/create', 'UserController@store')->name('users.create');

        Route::delete('/{id}', 'UserController@delete')->name('users.delete');
    });

    Route::group(['prefix' => 'identities'], function () {
        Route::get('/', 'IdentityController@index')->name('identities');

        Route::get('/create', 'IdentityController@create')->name('identities.create');

        Route::post('/create', 'IdentityController@store')->name('identities.store');

        Route::get('/{id}', 'IdentityController@edit')->name('identities.edit');

        Route::put('/{id}', 'IdentityController@update')->name('identities.edit');

        Route::delete('/{id}', 'IdentityController@delete')->name('identities.delete');
    });

    Route::group(['prefix' => 'cameras'], function () {
        Route::get('/', 'CameraController@index')->name('cameras');

        Route::get('/create', 'CameraController@create')->name('cameras.create');

        Route::post('/create', 'CameraController@store')->name('cameras.store');

        Route::get('/{id}', 'CameraController@edit')->name('cameras.edit');

        Route::put('/{id}', 'CameraController@update')->name('cameras.edit');

        Route::delete('/{id}', 'CameraController@delete')->name('cameras.delete');
    });

    Route::group(['prefix' => 'processes'], function () {
        Route::get('/', 'ProcessController@index')->name('processes');

        Route::get('/{id}', 'ProcessController@show')->name('processes.detail');

        Route::post('/create', 'ProcessController@store')->name('processes.create');

        Route::delete('/{id}', 'ProcessController@delete')->name('processes.delete');

        Route::post('/start-process', 'ProcessController@startProcess')->name('processes.start');

        Route::post('/stop-process', 'ProcessController@stopProcess')->name('processes.stop');

        Route::get('/{id}/objects', 'ProcessController@getObjects')->name('processes.objects');

        Route::get('/{id}/detail', 'ProcessController@getDetail')->name('processes.durations');

        Route::get('/{id}/export/before-grouping', 'ProcessController@exportBeforeGrouping')->name('processes.export.before-grouping');

        Route::get('/{id}/export/after-grouping', 'ProcessController@exportAfterGrouping')->name('processes.export.after-grouping');

        Route::post('/search-faces', 'ProcessController@searchFace')->name('processes.search-face');

        Route::post('/thumbnails', 'ProcessController@getThumbnail')->name('processes.thumbnail.create');
    });

    Route::group(['prefix' => 'objects'], function () {
        Route::post('/{id}/rendering', 'TrackedObjectController@startRendering');
    });

    Route::group(['prefix' => '/medias'], function () {
        Route::post('/', 'MediaController@create')->name('medias.create');
    });
});

// 404 for undefined routes
Route::any('/{page?}', function () {
    return View::make('pages.template.error.404');
})->where('page', '.*');
