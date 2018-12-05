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
    return view('admin.index');
});

//登录
Route::get('/login', function () {
    return view('admin.login');
});

//用户帐号管理
Route::get('/userBills', function () {
    return view('admin.userBills');
});

//达人管理
Route::get('/expertsList', function () {
    return view('admin.expertsList');
});

//组建团队管理
Route::get('/teamList', function () {
    return view('admin.teamList');
});

//团队成员
Route::get('/teamMember', function () {
    return view('admin.teamMember');
});

//订单管理
Route::get('/orderList', function () {
    return view('admin.orderList');
});

//go订单详情
Route::get('orderDetailGo', function () {
    return view('admin.orderDetailGo');
});

//联盟订单详情
Route::get('orderDetailAlliance', function () {
    return view('admin.orderDetailAlliance');
});

//分销管理
Route::get('/dealerList', function () {
    return view('admin.dealerList');
});

//提现管理
Route::get('/withdrawList', function () {
    return view('admin.withdrawList');
});

//账户流水
Route::get('/accountRecord', function () {
    return view('admin.accountRecord');
});

//提现字段设置
Route::get('/withdrawAlert', function () {
    return view('admin.withdrawAlert');
});

//提现申请记录
Route::get('/withdrawRecord', function () {
    return view('admin.withdrawRecord');
});

//产品管理
Route::get('/productList', function () {
    return view('admin.productList');
});

//新增商品
Route::get('/addProduct', function () {
    return view('admin.addProduct');
});

//修改商品
Route::get('/editProduct', function () {
    return view('admin.editProduct');
});

//商品详情
Route::get('/productDetail', function () {
    return view('admin.productDetail');
});

//组建团队设置
Route::get('/teamSet', function () {
    return view('admin.teamSet');
});

//邀请关注奖励设置
Route::get('/inviteSet', function () {
    return view('admin.inviteSet');
});

//邀请关注列表
Route::get('/inviteList', function () {
    return view('admin.inviteList');
});

//商家管理列表
Route::get('/businessList', function () {
    return view('admin.businessList');
});
