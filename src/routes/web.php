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


Route::group(['namespace' => 'App\Http\Controllers'], function () {
    Auth::routes(['register' => false]);
});

Route::group(['middleware' => ['auth'], 'namespace' => 'App\Http\Controllers'], function () {

    Route::get('/', 'DashboardController@index')->name('dashboard.index');

    Route::group(['prefix' => 'processes'], function () {
        Route::get('/{id}/detail', 'ProcessController@getDetail')->name('processes.durations');

        Route::get('/{id}/export/before-grouping', 'ProcessController@exportBeforeGrouping')->name('processes.export.before-grouping');

        Route::get('/{id}/export/after-grouping', 'ProcessController@exportAfterGrouping')->name('processes.export.after-grouping');

        Route::post('/search-faces', 'ProcessController@searchFace')->name('processes.search-face');
    });

    Route::group(['prefix' => 'objects'], function () {
        Route::post('/{id}/rendering', 'TrackedObjectController@startRendering');
    });

    Route::group(['prefix' => '/monitors'], function () {
        Route::get('/', 'MonitorController@index')->name('monitors');
        Route::post('/new-processes', 'MonitorController@getNewProcesses')->name('monitors.new-processes');
    });
});

// 404 for undefined routes
Route::any('/{page?}', function () {
    return View::make('pages.template.error.404');
})->where('page', '.*');
