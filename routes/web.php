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
Route::post('/product/plan/create/{id}', 'ProductController@createBillingAgreement')->name('create_billing_agreement');
Route::post('/product/plan/delete/{id}', 'ProductController@deleteBillingAgreement')->name('delete_billing_agreement');

// TODO: Get checkout and subscription routes working.
// checkout routes
Route::post('/paypal/create-checkout', 'ProductController@createCheckout');
Route::post('/paypal/execute-checkout', 'ProductController@executeCheckout')->name('execute-checkout');
// subscription routes
Route::post('/paypal/create-agreement/{id}', 'ProductController@createBillingAgreementCheckout');
Route::get('/paypal/execute-agreement/{success}', 'ProductController@executeBillingAgreementCheckout')->name('execute-agreement');

