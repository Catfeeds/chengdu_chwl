<?php

use Illuminate\Support\Facades\Route;

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
    return view('index');
});

Route::get('/business', function () {
    return view('index');
});

Route::get('/details', function () {
    return view('details');
});

Route::get('/poster', function () {
    return view('poster');
});

Route::get('/payment_order', function () {
    return view('payment_order');
});

Route::get('/user', function () {
    return view('user');
});

Route::get('/u_order', function () {
    return view('u_order');
});

Route::get('/burse', function () {
    return view('burse');
});

Route::get('/withdraw', function () {
    return view('withdraw');
});

Route::get('/talent_info', function () {
    return view('talent_info');
});

Route::get('/team', function () {
    return view('team');
});

Route::get('/talent', function () {
    return view('talent');
});







Route::get('/test', function () {
    return view('test');
});

Route::get('MP_verify_Ri9KkKtCO3sXk9Re.txt', function () {
  return 'Ri9KkKtCO3sXk9Re';
});
