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

Route::get('/', 'HomeController@index')->name('index');

Route::group(['prefix' => 'production'], function (){
    Route::get('/', 'SearchController@production')->name('production.index');

    Route::get ('/search', 'SearchController@production')->name('search');
    Route::post('/search', 'SearchController@search');
    Route::post('/store', 'SearchController@store')->name('store');
    Route::get ('/store', 'SearchController@storeGet');
});

Route::group(['prefix' => 'statistics'], function () {
    Route::get('/', 'StatisticsController@statistics')->name('statistics');
    Route::get('/download-file-day', 'StatisticsController@downloadFileDay')->name('statistics.downloadFileDay');

    Route::get('/download-file-month', 'StatisticsController@downloadFileMonth')
        ->middleware('admin:admin')
        ->name('statistics.downloadFileMonth');

    Route::post('/download-last-file', 'StatisticsController@downloadLastFile')
        ->middleware('admin:admin')
        ->name('statistics.downloadLastFile');
});

Route::get('/login', 'Auth\LoginController@loginGet')->name('login');
Route::post('/login', 'Auth\LoginController@loginPost');

Route::get('/logout', 'Auth\LoginController@logoutGet')->name('logout');
Route::post('/logout', 'Auth\LoginController@logout');

//Route::get('register', [\App\Http\Controllers\Auth\LoginController::class, 'register']);
