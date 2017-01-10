<?php

namespace App\Models\Channel;

class Alipay extends Base
{
    protected $table = 'channel_alipays';

    protected static function getFromDatabase($partnerId)
    {
        $data = app('db')->table('channel_alipays')
            ->select('appid', 'private_key', 'alipay_public_key')
            ->where('partner_id', $partnerId)
            ->first();

        return $data;
    }

    protected static function channelName()
    {
        return 'alipay';
    }
}