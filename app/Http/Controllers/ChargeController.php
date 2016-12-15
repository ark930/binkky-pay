<?php

namespace App\Http\Controllers;

use App\Libraries\Channel\Payment;
use App\Models\Charge;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    public function create(Request $request)
    {
        $charge = new Charge();
        $charge['channel'] = $request->input('channel');
        $charge['type'] = $request->input('type');
        $charge['order_no'] = $request->input('order_no');
        $charge['amount'] = $request->input('amount');
        $charge['currency'] = $request->input('currency');
        $charge->save();

        $payment = Payment::make($charge['channel'], $charge['type']);
        $payUrl = $payment->charge($charge);

        return response([
            'credential' => $payUrl
        ], 200);
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
        if($status === 'finish' || $status === 'close') {
            return response('success', 200);
        }

        $notify = $request->all();
        $payment = Payment::make($channel);
        $data = $payment->notify($charge, $notify);

        return response('success', 200);
    }
}