<?php

namespace App\Libraries\Channel;

use App\Models\Charge;
use App\Models\Refund;

interface IPayment
{
    public function charge(Charge $charge);

    public function query(Charge $charge);

    public function notify(Charge $charge, array $notify);

    public function refund(Charge $charge, Refund $refund);

    public function refundQuery(Charge $charge, Refund $refund);

}