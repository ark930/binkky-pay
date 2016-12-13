<?php

namespace App\Libraries\Channel;

interface IPayment
{
    public function create(array $chargeParams);
}