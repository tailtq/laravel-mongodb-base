<?php

Route::group(['middleware' => ['auth']], function () {
    Route::post('/{id}/rendering', 'ObjectController@startRendering');
});
