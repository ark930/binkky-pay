<?php

namespace App\Libraries\Channel\Alipay;

use App\Exceptions\BadRequestException;
use App\Models\Charge;

class AlipayQR extends AlipayBase
{
    public function charge(Charge $charge, array $params = [])
    {
        $bizContent = [
            'subject'           => $charge['title'],
            'body'              => $charge['desc'],
            'out_trade_no'      => $charge['trade_no'],
            'total_amount'      => $this->formatAmount($charge['amount']),
        ];

        if(!empty($charge['expired_at'])) {
            $bizContent['timeout_express'] = $this->makeExpiredTime($charge);
        }

        $commonParams = $this->makeCommonParameters($this->getAction('qrcode.pay'), $charge['created_at']);
        $commonParams['notify_url'] = $this->getNotifyUrl($charge['id']);
        $commonParams['biz_content'] = json_encode($bizContent);
        $response = $this->request($commonParams);
        $res = $this->parseResponse($response, $this->getResponseKey('qrcode.pay'));

        if($res['code'] === '10000' && $res['out_trade_no'] === $charge['trade_no']) {
            $this->credential = $res['qr_code'];
            return parent::charge($charge);
        }

        throw new BadRequestException('请求失败:' . $res['code']);
    }
}