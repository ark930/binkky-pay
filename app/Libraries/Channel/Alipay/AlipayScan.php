<?php

namespace App\Libraries\Channel\Alipay;

class AlipayScan extends AlipayBase
{
    const SCENE_BAR_CODE = 'bar_code';
    const SCENE_WAVE_CODE = 'wave_code';

    public function charge(array $chargeParams)
    {
        $bizContent = [
            'subject'           => $chargeParams['subject'],
            'body'              => $chargeParams['body'],
            'out_trade_no'      => $chargeParams['out_trade_no'],
            'timeout_express'   => $chargeParams['timeout_express'],
            'total_amount'      => $chargeParams['total_amount'],
            'scene'             => self::SCENE_BAR_CODE,
            'auth_code'         => $chargeParams['auth_code'],
        ];

        $commonParams = $this->makeCommonParameters(self::METHODS['scan.pay'], $chargeParams['timestamp']);
        $commonParams['notify_url'] = $chargeParams['notify_url'];
        $commonParams['biz_content'] = json_encode($bizContent);

        $requestUrl = $this->makeRequestUrl($commonParams);

        return $requestUrl;
    }
}