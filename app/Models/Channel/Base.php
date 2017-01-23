<?php

namespace App\Models\Channel;

use App\Exceptions\ChannelNotAvailableException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

abstract class Base extends Model
{
    public static function getPaymentParameters($partnerId)
    {
        $channelName = static::channelName();
        $redisKey = static::getRedisKey($channelName, $partnerId);

        $data = Redis::command('HGETALL', [$redisKey]);
        if(empty($data)) {
            $data = static::getFromDatabase($partnerId);
            if(empty($data)) {
                throw new ChannelNotAvailableException($channelName);
            }
            $data = collect($data)->toArray();

            $hashData = [$redisKey];
            foreach ($data as $k => $v) {
                $hashData[] = $k;
                $hashData[] = $v;
            }
            Redis::command('HMSET', $hashData);
        }

        return $data;
    }

    protected static function getRedisKey($channelName, $partnerId)
    {
        return $channelName . ':' . $partnerId;
    }

    abstract protected static function getFromDatabase($partnerId);

    abstract protected static function channelName();
}