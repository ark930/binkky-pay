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

$app->get('/', function () use ($app) {
    return $app->version();
});


$app->group(['prefix' => 'v1'], function() use ($app) {
    $app->post('charges', 'ChargeController@create');
    $app->get('charges/{charge_id}', 'ChargeController@retrieve');

    $app->post('refunds', 'RefundController@create');
    $app->get('refunds/{refund_id}', 'RefundController@retrieve');

    $app->group(['prefix' => 'notify'], function() use ($app) {
        $app->get('charges/{charge_id}', 'ChargeController@notify');
        $app->get('refunds/{refund_id}', 'RefundController@notify');
    });
});

