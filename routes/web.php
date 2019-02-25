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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/product/create', 'ProductController@createProduct')->name('create_product');
Route::get('/product/view/{id}', 'ProductController@index')->name('view_product');

// TODO: Get checkout and subscription routes working.
// checkout routes
Route::post('/paypal/create-checkout', 'PayPalHelper@CreateCheckout');
Route::post('/paypal/execute-checkout', 'PayPalHelper@ExecuteCheckout')->name('execute-checkout');
// subscription routes
Route::post('/paypal/create-agreement/{id}', 'PayPalHelper@CreateSubscription');
Route::get('/paypal/execute-agreement/{success}', 'PayPalHelper@ExecuteSubscription')->name('execute-agreement');

