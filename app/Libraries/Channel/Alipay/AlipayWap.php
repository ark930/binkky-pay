<?php

namespace App\Libraries\Channel\Alipay;

use App\Models\Charge;

class AlipayWap extends AlipayBase
{
    public function charge(Charge $charge)
    {
        $bizContent = [
            'subject'           => $charge['title'],
            'body'              => $charge['desc'],
            'out_trade_no'      => $charge['trade_no'],
            'timeout_express'   => $this->makeExpiredTime($charge['expired_at']),
            'total_amount'      => $this->formatAmount($charge['amount']),
            'product_code'      => 'QUICK_WAP_PAY',
        ];

        $commonParams = $this->makeCommonParameters($this->getAction('wap.pay'), $charge['created_at']);
        $commonParams['return_url'] = $charge['return_url'];
        $commonParams['notify_url'] = $this->makeNotifyUrl($charge['id']);
        $commonParams['biz_content'] = json_encode($bizContent);

        $requestUrl = $this->makeRequestUrl($commonParams);

        $this->credential = $requestUrl;
        return parent::charge($charge);
    }
}
