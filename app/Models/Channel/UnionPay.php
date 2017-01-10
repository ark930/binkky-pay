<?php

namespace App\Models\Channel;

class UnionPay extends Base
{
    protected $table = 'channel_union_pays';

    protected static function getFromDatabase($partnerId)
    {
        $data = app('db')->table('channel_union_pays')
            ->select('mer_id', 'cert_id', 'cert_private_key')
            ->where('partner_id', $partnerId)
            ->first();

        return $data;
    }

    protected static function channelName()
    {
        return 'union_pay';
    }
}