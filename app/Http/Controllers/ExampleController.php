<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Libraries\IPayment;
use App\Libraries\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function alipayWap(Request $request)
    {
        if($request->method() == 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Headers', 'X-BINKKY-KEY');
        }

        $key = $request->header('X-BINKKY-KEY');
        if(empty($key) || $key != '5gD5LQ3IZX') {
            throw new UnauthorizedException();
        }

        $amount = $request->input('amount');
        if(empty($amount) || !is_numeric($amount)) {
            $amount = '0.01';
        }

        $chargeParams = [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_amount' => $amount,
            'body' => '牛肉面',
            'subject' => '大碗牛肉面',
            'out_trade_no' => str_random(16),
            'timeout_express' => '90m',
            'return_url' => 'http://baidu.com',
            'notify_url' => 'http://baidu.com',
        ];

        $payment = Payment::make(Payment::CHANNEL_ALIPAY, Payment::PAYMENT_WAP);
        $payUrl = $payment->create($chargeParams);

//        return redirect($payUrl);
        return response($payUrl, 200)->header('Access-Control-Allow-Origin', '*');
    }

    public function wechatQR(Request $request)
    {
        if($request->method() == 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Headers', 'X-BINKKY-KEY');
        }

        $key = $request->header('X-BINKKY-KEY');
        if(empty($key) || $key != '5gD5LQ3IZX') {
            throw new UnauthorizedException();
        }

        $chargeParams = [
            'time_start' => date("YmdHis", time()),
            'client_ip' => '127.0.0.1',
            'amount' => 1,
            'body' => '牛肉面',
            'out_trade_no' => str_random(16),
            'product_id' => str_random(16),
            'notify_url' => 'http://baidu.com',
        ];

        $payment = Payment::make(Payment::CHANNEL_WECHAT, Payment::PAYMENT_QR);

        if($payment instanceof IPayment) {
            $payUrl = $payment->create($chargeParams);
            return response($payUrl, 200)->header('Access-Control-Allow-Origin', '*');
        }

        throw new BadRequestException('支付方式错误');
    }
}
