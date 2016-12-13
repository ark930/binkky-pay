<?php

namespace App\Libraries\Channel;

interface IPayment
{
    public function charge(array $chargeParams);
}