<?php

use Illuminate\Support\Facades\Route;

/*
 |--------------------------------------------------------------------------
 | API Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register API routes for your application. These
 | routes are loaded by the RouteServiceProvider within a group which
 | is assigned the "api" middleware group. Enjoy building your API!
 |
 */
//Route::resource('test', 'Admin\V1\TestController');

Route::group(['namespace' => 'Admin','middleware'=>['apiSign']], function(){
    Route::group(['namespace' => 'V1'], function(){
        //登录
        Route::resource('login', 'LoginController');
        
        Route::group(['middleware' => ['adminToken']], function(){
            //注册用户
            Route::resource('register', 'RegisterController');
            
            //用户管理
            Route::group(['namespace' => 'User'], function(){
                Route::resource('user', 'UserController');
                Route::resource('user_talent', 'UserTalentController');
                Route::resource('user_team', 'UserTeamController');
                Route::resource('order', 'OrderController');
                Route::resource('distribution', 'DistributionController');
                Route::resource('user_cash', 'UserCashController');
                Route::resource('user_talent_qrcode', 'UserTalentQrcodeController');
            });
                
            //联盟平台管理
            Route::group(['namespace' => 'Platform'], function(){
                Route::resource('product', 'ProductController');
                Route::resource('product_status', 'ProductStatusController');
                Route::resource('business_select', 'BusinessSelectController');
                Route::resource('product_city', 'ProductCityController');
                Route::resource('inviter_record', 'InviterRecordController');
                Route::resource('business', 'BusinessController');
            });
                    
            //后台设置
            Route::resource('admin_set', 'AdminSetController');
            
            //文件上传
            Route::resource('upload', 'UploadController');
        });
    });
});
    