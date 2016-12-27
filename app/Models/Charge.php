<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    // 渠道名称
    const CHANNEL_ALIPAY = 'alipay';
    const CHANNEL_WECHAT = 'wechat';

    // 支付类型
    const TYPE_QR = 'qr';
    const TYPE_SCAN = 'scan';
    const TYPE_PUB = 'pub';
    const TYPE_WAP = 'wap';

    // 支付状态
    const STATUS_PENDING= 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';
    const STATUS_CLOSED = 'closed';

    public function refunds()
    {
        return $this->hasMany('App\Models\Refund');
    }
}