<?php

namespace App\Models\Channel;

class Wechat extends Base
{
    protected $table = 'channel_wechats';

    protected static function getFromDatabase($partnerId)
    {
        $data = app('db')->table('channel_wechats')
            ->select('appid', 'mch_id', 'key')
            ->where('partner_id', $partnerId)
            ->first();

        return $data;
    }

    public static function channelName()
    {
        return 'wechat';
    }
}