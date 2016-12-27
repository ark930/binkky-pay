<?php

namespace App\Models\Channel;

class Alipay extends Base
{
    protected $table = 'channel_alipays';

    protected static function getFromDatabase()
    {
        $data = app('db')->table('channel_alipays')
            ->select('appid', 'private_key', 'alipay_public_key')
            ->first();

        return $data;
    }

    protected static function channelName()
    {
        return 'alipay';
    }
}