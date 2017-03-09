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
        $description = $request->input('description');

        $charge = Charge::findOrFail($chargeId);

        $refund = new Refund();
        $refund['trade_no'] = str_random(16);
        $refund['amount'] = $amount;
        $refund['currency'] = 'cny';
        $refund['description'] = $description;
        $refund->save();

        $channel = $charge['channel'];
        $status = $charge['status'];
        if($status === 'finish' || $status === 'close') {
            return $charge;
        }

        $payment = Payment::make($channel, $partnerId);
        $refund = $payment->refund($charge, $refund);

        return $refund;
    }

    public function query(Request $request, $refund_id)
    {
        $partnerId = $request->get('partner_id');

        $refund = Refund::findOrFail($refund_id);
        $charge = $refund->charge;

        $channel = $charge['channel'];
        $status = $charge['status'];
        if($status === 'finish' || $status === 'close') {
            return $charge;
        }

        $payment = Payment::make($channel, $partnerId);
        $refund = $payment->refundQuery($charge, $refund);

        return $refund;
    }
}