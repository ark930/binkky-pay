<?php

namespace App\Http\Controllers;

use App\Libraries\Channel\Payment;
use App\Models\Charge;
use App\Models\Refund;

class RefundController extends Controller
{
    public function create()
    {
        $charge_id = 1;
        $charge = Charge::findOrFail($charge_id);

        $refund = new Refund();
        $refund['order_no'] = str_random(16);
        $refund['amount'] = 1;
        $refund['currency'] = 'cny';

        $channel = $charge['channel'];
        $status = $charge['status'];
        if($status === 'finish' || $status === 'close') {
            return $charge;
        }

        $payment = Payment::make($channel);
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