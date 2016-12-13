<?php

namespace App\Libraries\Channel\Alipay;

use App\Models\Charge;

class AlipayQR extends AlipayBase
{
    public function charge(Charge $charge)
    {
        $bizContent = [
            'subject'           => $charge['subject'],
            'body'              => $charge['body'],
            'out_trade_no'      => $charge['order_no'],
            'timeout_express'   => $charge['expired_at'],
            'total_amount'      => $charge['amount'],
        ];

        $commonParams = $this->makeCommonParameters(self::METHODS['qrcode.pay'], $charge['created_at']);
        $commonParams['notify_url'] = $charge['notify_url'];
        $commonParams['biz_content'] = json_encode($bizContent);

        $requestUrl = $this->makeRequestUrl($commonParams);

        return $requestUrl;
    }
}