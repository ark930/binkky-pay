<?php

namespace App\Libraries\Channel\Alipay;

use App\Libraries\Channel\IPayment;

class AlipayWap extends AlipayBase implements IPayment
{
    public function create(array $chargeParams)
    {
        $bizContent = [
            'body'              => $chargeParams['body'],
            'subject'           => $chargeParams['subject'],
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