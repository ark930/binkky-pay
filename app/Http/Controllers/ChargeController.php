<?php

namespace App\Http\Controllers;

use App\Libraries\Channel\Payment;
use App\Models\Charge;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    public function create(Request $request)
    {
        // 参数验证
        $this->validate($request, [
            // 必要参数
            'channel'       => 'required|in:alipay,wechat',
            'type'          => 'required|in:qr,scan,wap,pub',
            'trade_no'      => 'required|min:8',
            'amount'        => 'required|integer',
            'currency'      => 'required|alpha',
            'title'         => 'required',
            'desc'          => 'required',
            'client_ip'     => 'required|ip',

            // 可选参数
            'expired_at'    => 'filled|date',
            'auth_code'     => 'filled',
            'open_id'       => 'filled',
        ]);

        $charge = new Charge();
        $charge['channel'] = $request->input('channel');
        $charge['type'] = $request->input('type');
        $charge['trade_no'] = $request->input('trade_no');
        $charge['amount'] = $request->input('amount');
        $charge['title'] = $request->input('title');
        $charge['desc'] = $request->input('desc');
        $charge['currency'] = $request->input('currency');
        $charge['client_ip'] = $request->input('client_ip');

        if($request->has('expired_at')) {
            $charge['expired_at'] = $request->input('expired_at');
        }
        if($charge['type'] == Charge::TYPE_SCAN) {
            $this->validate($request, [
                'auth_code' => 'required',
            ]);
            $charge['auth_code'] = $request->input('auth_code');
        }
        else if($charge['type'] == Charge::TYPE_PUB) {
            $this->validate($request, [
                'open_id' => 'required',
            ]);
            $charge['auth_code'] = $request->input('open_id');
        }
        $charge->save();

        if($request->hasHeader('X-Testing') && $request->header('X-Testing') == 'true') {
            $payment = Payment::makeTesting($charge['channel'], $charge['type']);
        } else {
            $payment = Payment::make($charge['channel'], $charge['type']);
        }

        $data = $payment->charge($charge);

        return response($data, 200);
    }

    public function query($charge_id)
    {
        $charge = Charge::findOrFail($charge_id);
        $channel = $charge['channel'];
        $status = $charge['status'];
        if($status === 'finish' || $status === 'close') {
            return $charge;
        }

        $payment = Payment::make($channel);
        $data = $payment->query($charge);

        return response($data, 200);
    }

    public function notify(Request $request, $charge_id)
    {
        $charge = Charge::findOrFail($charge_id);
        $channel = $charge['channel'];
        $status = $charge['status'];
        if($status === Charge::STATUS_SUCCEEDED || $status === Charge::STATUS_CLOSED) {
            return response('success', 200);
        }

        $notify = $request->all();
        $payment = Payment::make($channel);
        $data = $payment->notify($charge, $notify);

        return response($data, 200);
    }
}