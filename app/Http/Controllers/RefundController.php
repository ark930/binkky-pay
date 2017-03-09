<?php

namespace App\Http\Controllers;

use App\Libraries\Channel\Payment;
use App\Models\Charge;
use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function create(Request $request)
    {
        $partnerId = $request->get('partner_id');

        $chargeId = $request->input('charge_id');
        $amount = $request->input('amount');

        $charge = Charge::findOrFail($chargeId);

        $refund = new Refund();
        $refund['trade_no'] = str_random(16);
        $refund['amount'] = $amount;
        $refund['currency'] = 'cny';

        $channel = $charge['channel'];
        $status = $charge['status'];
        if($status === 'finish' || $status === 'close') {
            return $charge;
        }

        $payment = Payment::make($channel, $partnerId);
        $data = $payment->refund($charge, $refund);

        return $data;
    }

    public function query($refund_id)
    {
        $refund = Refund::findOrFail($refund_id);
        $charge = $refund->charge;

        $channel = $charge['channel'];
        $status = $charge['status'];
        if($status === 'finish' || $status === 'close') {
            return $charge;
        }

        $payment = Payment::make($channel);
        $data = $payment->refundQuery($charge, $refund);

        return response($data, 200);
    }
}