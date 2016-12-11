<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelAlipay extends Model
{
    public static function getPaymentParameters()
    {
        return app('db')->table('channel_alipays')
            ->select('appid', 'private_key', 'alipay_public_key')
            ->first();
    }
}