<?php

namespace App\Http\Controllers;

use App\Libraries\Channel\Payment;
use App\Models\Charge;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    public function create(Request $request)
    {
        $this->validate($request, [
            'channel' => 'required',
            'type' => 'required',
            'order_no' => 'required',
            'amount' => 'required',
            'currency' => 'required',
            'body' => 'required',
            'subject' => 'required',
        ]);

        $charge = new Charge();
        $charge['channel'] = $request->input('channel');
        $charge['type'] = $request->input('type');
        $charge['order_no'] = $request->input('order_no');
        $charge['amount'] = $request->input('amount');
        $charge['body'] = $request->input('body');
        $charge['subject'] = $request->input('subject');
        $charge['currency'] = $request->input('currency');

        if($charge['type'] == Charge::TYPE_SCAN) {
            $charge['auth_code'] = $request->input('auth_code');
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