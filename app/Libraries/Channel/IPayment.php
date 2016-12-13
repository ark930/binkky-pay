<?php

namespace App\Libraries\Channel;

use App\Models\Charge;

interface IPayment
{
    public function charge(Charge $charge);

    public function query(Charge $charge);

    public function notify(Charge $charge, array $notify);
}