<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelWechat extends Model
{
    public static function getPaymentParameters()
    {
        return app('db')->table('channel_wechats')
            ->select('appid', 'mch_id', 'key')
            ->first();
    }
}