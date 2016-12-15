<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class ChannelAlipay extends Model
{
    public static function getPaymentParameters()
    {
        $data = Redis::command('HGETALL', ['alipay']);
        if(empty($data)) {
            $data = app('db')->table('channel_alipays')
                ->select('appid', 'private_key', 'alipay_public_key')
                ->first();

            $data = collect($data)->toArray();

            $hashData = ['alipay'];
            foreach ($data as $k => $v) {
                $hashData[] = $k;
                $hashData[] = $v;
            }
            Redis::command('HMSET', $hashData);
        }

        return $data;
    }
}