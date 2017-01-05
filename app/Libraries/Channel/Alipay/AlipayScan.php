<?php

namespace App\Libraries\Channel\Alipay;

use App\Exceptions\BadRequestException;
use App\Models\Charge;

class AlipayScan extends AlipayBase
{
    const SCENE_BAR_CODE = 'bar_code';
    const SCENE_WAVE_CODE = 'wave_code';

    public function charge(Charge $charge)
    {
        $bizContent = [
            'subject'           => $charge['title'],
            'body'              => $charge['desc'],
            'out_trade_no'      => $charge['trade_no'],
            'total_amount'      => $this->formatAmount($charge['amount']),
            'scene'             => self::SCENE_BAR_CODE,
            'auth_code'         => $charge['auth_code'],
        ];

        if(!empty($charge['expired_at'])) {
            $bizContent['timeout_express'] = $this->makeExpiredTime($charge);
        }

        $commonParams = $this->makeCommonParameters($this->getAction('scan.pay'), $charge['created_at']);
        $commonParams['notify_url'] = $this->makeNotifyUrl($charge['id']);
        $commonParams['biz_content'] = json_encode($bizContent);
        $response = $this->request($commonParams);
        $res = $this->parseResponse($response, $this->getResponseKey('scan.pay'));

        if($res['code'] === '10000' && $res['out_trade_no'] === $charge['trade_no']) {
            $this->credential = $res['qr_code'];
            return parent::charge($charge);
        }

        throw new BadRequestException('请求失败:' . $res['code'] . '=>' . $res['sub_msg']);
    }
}