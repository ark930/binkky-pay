<?php

namespace App\Models\Channel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

abstract class Base extends Model
{
    public static function getPaymentParameters($partnerId)
    {
        $channelName = static::channelName();
        $data = Redis::command('HGETALL', [$channelName]);
        if(empty($data)) {
            $data = static::getFromDatabase($partnerId);
            $data = collect($data)->toArray();

            $hashData = [$channelName];
            foreach ($data as $k => $v) {
                $hashData[] = $k;
                $hashData[] = $v;
            }
            Redis::command('HMSET', $hashData);
        }

        return $data;
    }

    abstract protected static function getFromDatabase($partnerId);

    abstract protected static function channelName();
}