<?php

namespace App\Libraries\Channel\Alipay;

class AlipayQR extends AlipayBase
{
    public function charge(array $chargeParams)
    {
        $bizContent = [
            'subject'           => $chargeParams['subject'],
            'body'              => $chargeParams['body'],
            'out_trade_no'      => $chargeParams['out_trade_no'],
            'timeout_express'   => $chargeParams['timeout_express'],
            'total_amount'      => $chargeParams['total_amount'],
        ];

        $commonParams = $this->makeCommonParameters(self::METHODS['qrcode.pay'], $chargeParams['timestamp']);
        $commonParams['notify_url'] = $chargeParams['notify_url'];
        $commonParams['biz_content'] = json_encode($bizContent);

        $requestUrl = $this->makeRequestUrl($commonParams);

        return $requestUrl;
    }
}