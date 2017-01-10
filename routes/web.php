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

$app->group(['prefix' => 'v1'], function() use ($app) {
    $app->post('charges', 'ChargeController@create');
    $app->get('charges/{charge_id}', 'ChargeController@query');
//    $app->get('charges/{charge_id}/close', 'ChargeController@close');
    $app->get('charges/{charge_id}/notify', ['as' => 'notify', 'uses' => 'ChargeController@notify']);
    $app->post('charges/{charge_id}/notify', 'ChargeController@notify');

    $app->post('refunds', 'RefundController@create');
    $app->get('refunds/{refund_id}', 'RefundController@query');

//    $app->get('charges/bill', 'ChargeController@bill');

    $app->group(['prefix' => 'channels'], function() use($app) {
        $app->post('alipay', 'Channels\AlipayController@store');
        $app->get('alipay', 'Channels\AlipayController@show');
        $app->post('wechat', 'Channels\WechatController@store');
        $app->get('wechat', 'Channels\WechatController@show');
    });

    $app->group(['prefix' => 'keys'], function() use($app) {
        $app->post('/', 'KeyController@store');
        $app->get('/{partner_id}', 'KeyController@show');
        $app->put('/{partner_id}', 'KeyController@update');
    });
});

$app->options('/alipay_wap', 'ExampleController@alipayWap');
$app->get('/alipay_wap', 'ExampleController@alipayWap');

$app->options('/wechat_qr', 'ExampleController@wechatQR');
$app->get('/wechat_qr', 'ExampleController@wechatQR');
