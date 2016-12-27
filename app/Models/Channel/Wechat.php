<?php

namespace App\Models\Channel;

class Wechat extends Base
{
    protected $table = 'channel_wechats';

    protected static function getFromDatabase()
    {
        $data = app('db')->table('channel_wechats')
            ->select('appid', 'mch_id', 'key')
            ->first();

        return $data;
    }

    public static function channelName()
    {
        return 'wechat';
    }
}