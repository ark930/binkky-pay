<?php

namespace App\Http\Controllers;

use App\Libraries\Channel\Helper;
use App\Libraries\Channel\Payment;
use App\Libraries\HttpClient;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChargeController extends Controller
{
    public function create(Request $request)
    {
        $partnerId = $request->get('partner_id');

        // 参数验证
        $this->validate($request, [
            // 必要参数
            'channel'       => 'required|in:alipay,wechat,union_pay',
            'type'          => 'required|in:qr,scan,wap,pub,app',
            'trade_no'      => 'required|min:8',
            'amount'        => 'required|integer',
            'currency'      => 'required|alpha',
            'title'         => 'required',
            'desc'          => 'required',
            'client_ip'     => 'required|ip',
            'notify_url'    => 'required|url',

            // 可选参数
            'expired_at'    => 'filled|date',
            'auth_code'     => 'filled',
            'open_id'       => 'filled',
        ]);

        $charge = new Charge();
        $charge['partner_id'] = $partnerId;
        $charge['channel'] = $request->input('channel');
        $charge['type'] = $request->input('type');
        $charge['trade_no'] = $request->input('trade_no');
        $charge['amount'] = $request->input('amount');
        $charge['title'] = $request->input('title');
        $charge['desc'] = $request->input('desc');
        $charge['currency'] = $request->input('currency');
        $charge['client_ip'] = $request->input('client_ip');
        $charge['notify_url'] = $request->input('notify_url');

        if($request->has('expired_at')) {
            if(strtotime($request->input('expired_at')) > time()) {
                $charge['expired_at'] = $request->input('expired_at');
            }
        }

        $params = [];
        if($charge['type'] == Charge::TYPE_SCAN) {
            $this->validate($request, [
                'auth_code' => 'required',
            ]);
            $params['auth_code'] = $request->input('auth_code');
        }
        else if($charge['type'] == Charge::TYPE_PUB) {
            $this->validate($request, [
                'open_id' => 'required',
            ]);
            $params['auth_code'] = $request->input('open_id');
        }
        $charge->save();

        if($request->get('is_testing')) {
            $payment = Payment::makeTesting($charge['channel'], $partnerId, $charge['type']);
        } else {
            $payment = Payment::make($charge['channel'], $partnerId, $charge['type']);
        }

        $charge = $payment->charge($charge, $params);

        return response($charge, 200);
    }

    public function query(Request $request, $charge_id)
    {
        $charge = Charge::findOrFail($charge_id);
        $partnerId = $charge['partner_id'];
        $channel = $charge['channel'];
        $status = $charge['status'];
        if($status === 'finish' || $status === 'close') {
            return $charge;
        }

        if($request->get('is_testing')) {
            $payment = Payment::makeTesting($channel, $partnerId);
        } else {
            $payment = Payment::make($channel, $partnerId);
        }
        $charge = $payment->query($charge);

        return response($charge, 200);
    }

    public function notify(Request $request, $chargeId)
    {
        // 记录日志
        Log::info(PHP_EOL . $request->method() . ' ' . $request->fullUrl() . PHP_EOL
            . $request->getContentType() . PHP_EOL
            . \GuzzleHttp\json_encode($request->all(), JSON_PRETTY_PRINT) . PHP_EOL
            . $request->getContent() . PHP_EOL);

        // 如果已支付完成，直接返回 success
        $charge = Charge::findOrFail($chargeId);
        $channel = $charge['channel'];
        $status = $charge['status'];
        if($status === Charge::STATUS_SUCCEEDED || $status === Charge::STATUS_CLOSED) {
            return $this->notifyResponse($charge);
        }

        // 验证并处理渠道通知
        if($charge['channel'] === Charge::CHANNEL_WECHAT) {
            $notify = $request->getContent();
            $notify = Helper::xmlToArray($notify);
        } else {
            $notify = $request->all();
        }

        $partnerId = $charge['partner_id'];
        $payment = Payment::make($channel, $partnerId);
        $charge = $payment->notify($charge, $notify);

        // TODO 通知商户。后面要改为异步处理
        $client = new HttpClient();
        $client->initHttpClient();
        $res = $client->requestJson('POST', $charge['notify_url'], $charge);

        return $this->notifyResponse($charge);
    }

    public function queryBill(Request $request)
    {
        $partnerId = $request->get('partner_id');

        $payment = Payment::make('alipay', $partnerId);
        return $payment->billQuery(['bill_type'=>'signcustomer', 'bill_date' => '2017-03-08']);
    }

    protected function notifyResponse(Charge $charge)
    {
        if($charge['channel'] === Charge::CHANNEL_WECHAT) {
            $success = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            return response($success, 200);
        } else {
            return response('success', 200);
        }
    }
}