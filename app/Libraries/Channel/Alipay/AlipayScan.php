<?php

namespace App\Libraries\Channel\Alipay;

use App\Models\Charge;

class AlipayScan extends AlipayBase
{
    const SCENE_BAR_CODE = 'bar_code';
    const SCENE_WAVE_CODE = 'wave_code';

    public function charge(Charge $charge)
    {
        $bizContent = [
            'subject'           => $charge['subject'],
            'body'              => $charge['body'],
            'out_trade_no'      => $charge['order_no'],
            'timeout_express'   => $this->makeExpiredTime($charge['expired_at']),
            'total_amount'      => $this->formatAmount($charge['amount']),
            'scene'             => self::SCENE_BAR_CODE,
            'auth_code'         => $charge['auth_code'],
        ];

        $commonParams = $this->makeCommonParameters(self::METHODS['scan.pay'], $charge['created_at']);
        $commonParams['notify_url'] = $this->makeNotifyUrl($charge['id']);
        $commonParams['biz_content'] = json_encode($bizContent);

        $requestUrl = $this->makeRequestUrl($commonParams);

        return $requestUrl;
    }
}