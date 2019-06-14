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

use Illuminate\Support\Facades\Route;

Route::get('/', 'MainController@home')->name('home');
Route::get('/login', 'MainController@login')->name('login');
Route::get('/test', 'TestController@index')->name('test');

Route::prefix('merchant')->namespace('Merchant')->name('merchant.')->group(function () {
    Route::get('/', 'MerchantCardController@index')->name('card');
    Route::get('/managers/', 'OperatorController@list')->name('operator_list');
    Route::get('/stores/', 'StoreController@list')->name('store_list');
});

Route::prefix('ajax')->group(function () {
    Route::get('login', 'MainController@loginAjax')->name('login-ajax');
    Route::get('logout', 'MainController@logoutAjax')->name('logout-ajax');
});
