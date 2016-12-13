<?php

namespace App\Libraries\Channel\Alipay;

class AlipayWap extends AlipayBase
{
    public function charge(array $chargeParams)
    {
        $bizContent = [
            'subject'           => $chargeParams['subject'],
            'body'              => $chargeParams['body'],
            'out_trade_no'      => $chargeParams['out_trade_no'],
            'timeout_express'   => $chargeParams['timeout_express'],
            'total_amount'      => $chargeParams['total_amount'],
            'product_code'      => 'QUICK_WAP_PAY',
        ];

        $commonParams = $this->makeCommonParameters(self::METHODS['wap.pay'], $chargeParams['timestamp']);
        $commonParams['return_url'] = $chargeParams['return_url'];
        $commonParams['notify_url'] = $chargeParams['notify_url'];
        $commonParams['biz_content'] = json_encode($bizContent);

        $requestUrl = $this->makeRequestUrl($commonParams);

        return $requestUrl;
    }
}