<?php

namespace App\Libraries\Channel\Alipay;

use App\Exceptions\BadRequestException;
use App\Models\Charge;

class AlipayApp extends AlipayBase
{
    public function charge(Charge $charge, array $params = [])
    {
        $bizContent = [
            'subject' => $charge['title'],
            'body' => $charge['desc'],
            'out_trade_no' => $charge['trade_no'],
            'total_amount' => $this->formatAmount($charge['amount']),
            'product_code'      => 'QUICK_MSECURITY_PAY',
        ];

        if (!empty($charge['expired_at'])) {
            $bizContent['timeout_express'] = $this->makeExpiredTime($charge);
        }

        $commonParams = $this->makeCommonParameters($this->getAction('app.pay'), $charge['created_at']);
        $commonParams['notify_url'] = $this->getNotifyUrl($charge['id']);
        $commonParams['biz_content'] = json_encode($bizContent);

//        $preSignStr = $this->getSignContentWithUrlEncode($commonParams);
        $preSignStr = $this->getSignContent($commonParams);
        $sign =  $this->sign($preSignStr, $this->privateKey);
        $this->credential = $preSignStr."&sign=".urlencode($sign);

        return parent::charge($charge);
    }
}