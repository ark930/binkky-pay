<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', 'ExampleController@test');

$app->group(['middleware' => 'auth', 'prefix' => 'v1'], function() use ($app) {
    // 支付接口
    $app->post('charges', 'ChargeController@create');

    // 支付查询接口
    $app->get('charges/{charge_id}', 'ChargeController@query');

    // 关闭支付接口
//    $app->get('charges/{charge_id}/close', 'ChargeController@close');

    // 支付异步通知接口
    $app->get('charges/{charge_id}/notify', ['as' => 'notify', 'uses' => 'ChargeController@notify']);
    $app->post('charges/{charge_id}/notify', 'ChargeController@notify');

    // 退款接口
    $app->post('refunds', 'RefundController@create');
    $app->get('refunds/{refund_id}', 'RefundController@query');

    // 对账接口
//    $app->get('charges/bill', 'ChargeController@bill');

    // 支付渠道参数接口
    $app->group(['prefix' => 'channels'], function() use($app) {
        $app->post('alipay', 'Channels\AlipayController@store');
        $app->get('alipay', 'Channels\AlipayController@show');
        $app->post('wechat', 'Channels\WechatController@store');
        $app->get('wechat', 'Channels\WechatController@show');
    });

//    $app->group(['prefix' => 'keys'], function() use($app) {
//        $app->post('/', 'KeyController@store');
//        $app->get('/{partner_id}', 'KeyController@show');
//        $app->put('/{partner_id}', 'KeyController@update');
//    });
});

$app->get('/alipay_wap', 'ExampleController@alipayWap');

$app->get('/wechat_qr', 'ExampleController@wechatQR');
