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

// Example Routes
Route::view('/', 'landing');
Route::match(['get', 'post'], '/dashboard', function(){
    return view('dashboard');
});

Route::get('/person', 'PersonController@index');
Route::post('/person/get-main-datasets', 'PersonController@getMainDatasets');
Route::get('/person/datasets/{datasets}', 'PersonController@datasets');
Route::post('/person/reload-data/{datasets}', 'PersonController@reload');
Route::get('/person/get-datasets/{datasets}', 'PersonController@getDatasets');

Route::get('/corporation', 'CorporationController@index');
Route::post('/corporation/get-main-datasets', 'CorporationController@getMainDatasets');
Route::get('/corporation/datasets/{datasets}', 'CorporationController@datasets');
Route::post('/corporation/reload-data/{datasets}', 'CorporationController@reload');
Route::get('/corporation/get-datasets/{datasets}', 'CorporationController@getDatasets');

Route::get('/advanced-search', 'AdvancedSearchController@index');

Route::get('/general-search', 'GeneralSearchController@index');

Route::get('/address', 'AddressController@index');
Route::post('/address/get-main-datasets', 'AddressController@getMainDatasets');
Route::get('/address/datasets/{datasets}', 'AddressController@datasets');
Route::post('/address/reload-data/{datasets}', 'AddressController@reload');
Route::get('/address/get-datasets/{datasets}', 'AddressController@getDatasets');

Route::get('/abandon', 'AbandonController@index');
Route::post('/abandon/get-main-datasets', 'AbandonController@getMainDatasets');
Route::get('/abandon/datasets/{datasets}', 'AbandonController@datasets');
Route::post('/abandon/reload-data/{datasets}', 'AbandonController@reload');
Route::get('/abandon/get-datasets/{datasets}', 'AbandonController@getDatasets');

Route::get('/map', 'MapController@index');

Route::get('/link', 'LinkController@index');

Route::get('/zip', 'AddressController@zip');