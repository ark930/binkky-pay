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
            'timeout_express'   => $this->makeExpiredTime($charge['expired_at']),
            'total_amount'      => $this->formatAmount($charge['amount']),
        ];

        $commonParams = $this->makeCommonParameters(self::METHODS['qrcode.pay'], $charge['created_at']);
        $commonParams['notify_url'] = $this->makeNotifyUrl($charge['id']);
        $commonParams['biz_content'] = json_encode($bizContent);

        $requestUrl = $this->makeRequestUrl($commonParams);

        return $requestUrl;
    }
}