<?php

namespace App\Libraries\Channel;

use App\Models\Charge;
use App\Models\Refund;

abstract class IPayment
{
    abstract public function charge(Charge $charge);

    abstract public function query(Charge $charge);

    abstract public function notify(Charge $charge, array $notify);

    abstract public function refund(Charge $charge, Refund $refund);

    abstract public function refundQuery(Charge $charge, Refund $refund);

    protected function makeNotifyUrl($charge_id)
    {
        return route('notify', ['charge_id' => $charge_id]);
    }
}