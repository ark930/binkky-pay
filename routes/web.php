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

$app->get('/', function() use ($app) {
    return $app->version();
});

$app->group(['prefix' => 'v1'], function() use ($app) {
    $app->group(['middleware' => 'auth'], function() use ($app) {

        // 支付类接口
        $app->group(['prefix' => 'charges'], function() use ($app) {
            // 支付接口
            $app->post('/', 'ChargeController@create');

            // 支付查询接口
            $app->get('{charge_id}', 'ChargeController@query');

            // 关闭支付接口
            //    $app->get('{charge_id}/close', 'ChargeController@close');

            // 支付异步通知接口
            $app->get('{charge_id}/notify', ['as' => 'notify', 'uses' => 'ChargeController@notify']);
            $app->post('{charge_id}/notify', 'ChargeController@notify');

            // 对账接口
            //    $app->get('bill', 'ChargeController@bill');
        });

        // 退款类接口
        $app->group(['prefix' => 'refunds'], function() use ($app) {
            // 退款接口
            $app->post('/', 'RefundController@create');
            $app->get('{refund_id}', 'RefundController@query');
        });

        // 对账接口
        $app->group(['prefix' => 'bill'], function() use($app) {
            $app->get('{channel}', 'ChargeController@queryBill');
        });

        // 支付渠道参数类接口
        $app->group(['prefix' => 'channels'], function() use($app) {
            $app->post('alipay', 'Channels\AlipayController@store');
            $app->get('alipay', 'Channels\AlipayController@show');
            $app->post('wechat', 'Channels\WechatController@store');
            $app->get('wechat', 'Channels\WechatController@show');
        });
    });

    // API认证参数类接口
    $app->group(['prefix' => 'keys', 'middleware' => 'merchant.auth'], function() use($app) {
        $app->post('/', 'KeyController@store');
        $app->get('{partner_id}', 'KeyController@show');
        //   $app->put('/keys/{partner_id}', 'KeyController@update');
    });
});
