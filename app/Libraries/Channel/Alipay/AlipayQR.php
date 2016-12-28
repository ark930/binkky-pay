<?php

namespace App\Libraries\Channel\Alipay;

use App\Exceptions\BadRequestException;
use App\Models\Charge;

class AlipayQR extends AlipayBase
{
    public function charge(Charge $charge)
    {
        $bizContent = [
            'subject'           => $charge['title'],
            'body'              => $charge['desc'],
            'out_trade_no'      => $charge['trade_no'],
            'timeout_express'   => $this->makeExpiredTime($charge['expired_at']),
            'total_amount'      => $this->formatAmount($charge['amount']),
        ];

        $commonParams = $this->makeCommonParameters($this->getAction('qrcode.pay'), $charge['created_at']);
        $commonParams['notify_url'] = $this->makeNotifyUrl($charge['id']);
        $commonParams['biz_content'] = json_encode($bizContent);

        $res = $this->request($commonParams);

        $res = $res[$this->getResponseKey('qrcode.pay')];
        if($res['code'] === '10000' && $res['out_trade_no'] === $charge['trade_no']) {
            $this->credential = $res['qr_code'];
            return parent::charge($charge);
        }

        throw new BadRequestException('请求失败:' . $res['code']);
    }
}