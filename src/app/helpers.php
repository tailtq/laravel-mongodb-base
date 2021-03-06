<?php

use MongoDB\BSON\UTCDateTime;

function active_class($path, $active = 'active') {
  return call_user_func_array('Request::is', (array)$path) ? $active : '';
}

function is_active_route($path) {
  return call_user_func_array('Request::is', (array)$path) ? 'true' : 'false';
}

function show_class($path) {
  return call_user_func_array('Request::is', (array)$path) ? 'show' : '';
}

function my_asset($path) {
    return asset($path);
//    $isHttps = strpos(env('APP_URL'), 'https');

//    return app('url')->asset($path, $isHttps);
}

function dateNow(){
    return new MongoDB\BSON\UTCDateTime();
}
