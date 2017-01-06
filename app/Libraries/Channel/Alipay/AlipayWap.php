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
            'total_amount'      => $this->formatAmount($charge['amount']),
            'product_code'      => 'QUICK_WAP_PAY',
        ];

        if(!empty($charge['expired_at'])) {
            $bizContent['timeout_express'] = $this->makeExpiredTime($charge);
        }

        $commonParams = $this->makeCommonParameters($this->getAction('wap.pay'), $charge['created_at']);
        $commonParams['return_url'] = $this->getReturnUrl();
        $commonParams['notify_url'] = $this->getNotifyUrl($charge['id']);
        $commonParams['biz_content'] = json_encode($bizContent);

        $requestUrl = $this->makeRequestUrl($commonParams);

        $this->credential = $requestUrl;
        return parent::charge($charge);
    }

    protected function getReturnUrl()
    {
        return '';
    }
}
