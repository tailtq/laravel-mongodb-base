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

    Route::group(['prefix' => 'processes'], function () {
        Route::get('/', 'ProcessController@index')->name('processes');

        Route::get('/{id}', 'ProcessController@show')->name('processes.detail');

        Route::post('/create', 'ProcessController@store')->name('processes.create');

        Route::post('/start-process', 'ProcessController@startProcess')->name('processes.start');

        Route::post('/stop-process', 'ProcessController@stopProcess')->name('processes.stop');

        Route::get('/{id}/objects', 'ProcessController@getObjects')->name('processes.objects');
    });

    Route::group(['prefix' => '/medias'], function () {
        Route::post('/', 'MediaController@create')->name('medias.create');
    });
});

// Template layer

//Route::group(['prefix' => 'email'], function(){
//    Route::get('inbox', function () { return view('pages.template.email.inbox'); });
//    Route::get('read', function () { return view('pages.template.email.read'); });
//    Route::get('compose', function () { return view('pages.template.email.compose'); });
//});
//
//Route::group(['prefix' => 'apps'], function(){
//    Route::get('chat', function () { return view('pages.template.apps.chat'); });
//    Route::get('calendar', function () { return view('pages.template.apps.calendar'); });
//});
//
//Route::group(['prefix' => 'ui-components'], function(){
//    Route::get('alerts', function () { return view('pages.template.ui-components.alerts'); });
//    Route::get('badges', function () { return view('pages.template.ui-components.badges'); });
//    Route::get('breadcrumbs', function () { return view('pages.template.ui-components.breadcrumbs'); });
//    Route::get('buttons', function () { return view('pages.template.ui-components.buttons'); });
//    Route::get('button-group', function () { return view('pages.template.ui-components.button-group'); });
//    Route::get('cards', function () { return view('pages.template.ui-components.cards'); });
//    Route::get('carousel', function () { return view('pages.template.ui-components.carousel'); });
//    Route::get('collapse', function () { return view('pages.template.ui-components.collapse'); });
//    Route::get('dropdowns', function () { return view('pages.template.ui-components.dropdowns'); });
//    Route::get('list-group', function () { return view('pages.template.ui-components.list-group'); });
//    Route::get('media-object', function () { return view('pages.template.ui-components.media-object'); });
//    Route::get('modal', function () { return view('pages.template.ui-components.modal'); });
//    Route::get('navs', function () { return view('pages.template.ui-components.navs'); });
//    Route::get('navbar', function () { return view('pages.template.ui-components.navbar'); });
//    Route::get('pagination', function () { return view('pages.template.ui-components.pagination'); });
//    Route::get('popovers', function () { return view('pages.template.ui-components.popovers'); });
//    Route::get('progress', function () { return view('pages.template.ui-components.progress'); });
//    Route::get('scrollbar', function () { return view('pages.template.ui-components.scrollbar'); });
//    Route::get('scrollspy', function () { return view('pages.template.ui-components.scrollspy'); });
//    Route::get('spinners', function () { return view('pages.template.ui-components.spinners'); });
//    Route::get('tabs', function () { return view('pages.template.ui-components.tabs'); });
//    Route::get('tooltips', function () { return view('pages.template.ui-components.tooltips'); });
//});
//
//Route::group(['prefix' => 'advanced-ui'], function(){
//    Route::get('cropper', function () { return view('pages.template.advanced-ui.cropper'); });
//    Route::get('owl-carousel', function () { return view('pages.template.advanced-ui.owl-carousel'); });
//    Route::get('sweet-alert', function () { return view('pages.template.advanced-ui.sweet-alert'); });
//});
//
//Route::group(['prefix' => 'forms'], function(){
//    Route::get('basic-elements', function () { return view('pages.template.forms.basic-elements'); });
//    Route::get('advanced-elements', function () { return view('pages.template.forms.advanced-elements'); });
//    Route::get('editors', function () { return view('pages.template.forms.editors'); });
//    Route::get('wizard', function () { return view('pages.template.forms.wizard'); });
//});
//
//Route::group(['prefix' => 'charts'], function(){
//    Route::get('apex', function () { return view('pages.template.charts.apex'); });
//    Route::get('chartjs', function () { return view('pages.template.charts.chartjs'); });
//    Route::get('flot', function () { return view('pages.template.charts.flot'); });
//    Route::get('morrisjs', function () { return view('pages.template.charts.morrisjs'); });
//    Route::get('peity', function () { return view('pages.template.charts.peity'); });
//    Route::get('sparkline', function () { return view('pages.template.charts.sparkline'); });
//});
//
//Route::group(['prefix' => 'tables'], function(){
//    Route::get('basic-tables', function () { return view('pages.template.tables.basic-tables'); });
//    Route::get('data-table', function () { return view('pages.template.tables.data-table'); });
//});
//
//Route::group(['prefix' => 'icons'], function(){
//    Route::get('feather-icons', function () { return view('pages.template.icons.feather-icons'); });
//    Route::get('flag-icons', function () { return view('pages.template.icons.flag-icons'); });
//    Route::get('mdi-icons', function () { return view('pages.template.icons.mdi-icons'); });
//});
//
//Route::group(['prefix' => 'general'], function(){
//    Route::get('blank-page', function () { return view('pages.template.general.blank-page'); });
//    Route::get('faq', function () { return view('pages.template.general.faq'); });
//    Route::get('invoice', function () { return view('pages.template.general.invoice'); });
//    Route::get('profile', function () { return view('pages.template.general.profile'); });
//    Route::get('pricing', function () { return view('pages.template.general.pricing'); });
//    Route::get('timeline', function () { return view('pages.template.general.timeline'); });
//});
//
//Route::group(['prefix' => 'error'], function(){
//    Route::get('404', function () { return view('pages.template.error.404'); });
//    Route::get('500', function () { return view('pages.template.error.500'); });
//});
//
//Route::get('/clear-cache', function() {
//    Artisan::call('cache:clear');
//    return "Cache is cleared";
//});

// 404 for undefined routes
Route::any('/{page?}',function(){
    return View::make('pages.template.error.404');
})->where('page','.*');
