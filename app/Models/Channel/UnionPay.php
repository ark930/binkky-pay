<?php

namespace App\Models\Channel;

class UnionPay extends Base
{
    protected $table = 'channel_union_pays';

    protected static function getFromDatabase()
    {
        $data = app('db')->table('channel_union_pays')
            ->select('mer_id', 'cert_id', 'cert', 'cert_password')
            ->first();

        return $data;
    }

    protected static function channelName()
    {
        return 'union_pay';
    }
}