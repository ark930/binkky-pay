<?php

namespace App\Libraries;

interface IPayment
{
    public function create(array $chargeParams);
}